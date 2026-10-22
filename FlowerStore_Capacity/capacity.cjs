// Node.js built-ins only. Run with Node 24 (or another recent Node release).
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {spawnSync} = require('node:child_process');
const root = __dirname;
function csvParse(s) {
  const rows=[]; let row=[], value='', quoted=false;
  s=s.replace(/^\uFEFF/,'');
  for(let i=0;i<s.length;i++) {
    const c=s[i];
    if(c==='"') { if(quoted && s[i+1]==='"') {value+='"';i++;} else quoted=!quoted; }
    else if(!quoted && (c===',' || c==='\n')) {row.push(value);value='';if(c==='\n'){if(row.some(Boolean))rows.push(row);row=[];}}
    else if(c!=='\r' || quoted) value+=c;
  }
  if(quoted)throw Error('CSV has an unclosed quote');
  if(value || row.length){row.push(value);rows.push(row);}
  return rows;
}
const quantile=(xs,p)=>xs.length?[...xs].sort((a,b)=>a-b)[Math.ceil(xs.length*p)-1]:null;
function analyze(dir) {
  const meta=JSON.parse(fs.readFileSync(path.join(dir,'run.json'),'utf8'));
  const rows=csvParse(fs.readFileSync(path.join(dir,'results.jtl'),'utf8'));
  const head=rows.shift();
  for(const k of ['timeStamp','elapsed','label','responseCode','success','failureMessage','allThreads'])
    if(!head.includes(k))throw Error('Missing original JTL column: '+k);
  const samples=rows.map(r=>Object.fromEntries(head.map((k,i)=>[k,r[i]??''])));
  const http=samples.filter(r=>r.label==='REG GET form'||r.label==='REG POST strict SRS redirect');
  if(!http.length)throw Error('No registration HTTP samples');
  const post=http.filter(r=>r.label.startsWith('REG POST'));
  const transport=http.filter(r=>!/^\d{3}$/.test(r.responseCode));
  const known=post.filter(r=>r.responseCode==='302'&&r.failureMessage.startsWith('SRS_REDIRECT:'));
  const nominal=post.filter(r=>r.responseCode==='302'&&r.success==='true');
  const unexplained=http.filter(r=>/^\d{3}$/.test(r.responseCode)&&r.success!=='true'&&!known.includes(r));
  const start=Math.min(...http.map(r=>+r.timeStamp));
  const end=Math.max(...http.map(r=>+r.timeStamp + +r.elapsed));
  const duration=(end-start)/1000;
  const p95=quantile(post.map(r=>+r.elapsed),.95);
  const maxThreads=Math.max(...http.map(r=>+r.allThreads));
  const finalMinute=post.filter(r=>(+r.timeStamp-start)/1000>=120);
  const rate=transport.length/http.length;
  const issues=[];
  if(duration<170)issues.push('Run shorter than 170 s: check EOF, manual stop, scheduler and JMeter log');
  if(maxThreads!==meta.users)issues.push('Observed thread count differs from configured users');
  if(post.length<20)issues.push('Fewer than 20 POST samples');
  if(!finalMinute.length)issues.push('No POST sample started in the final minute');
  if(unexplained.length)issues.push('Unexpected flow, CSRF/validation, HTTP or script failures require investigation');
  if(fs.existsSync(path.join(dir,'jmeter.log'))&&/ERROR.*(?:JSR223|Exception|Error invoking)/i.test(fs.readFileSync(path.join(dir,'jmeter.log'),'utf8')))
    issues.push('JMeter runtime script error detected; inspect jmeter.log');
  const meets=rate<=.01&&p95!==null&&p95<=10000;
  const result={test:'JM-REG-003-CAP',users:meta.users,run:meta.run,durationSeconds:duration,maxObservedThreads:maxThreads,
    httpRequests:http.length,postRequests:post.length,transportErrors:transport.length,transportErrorPercent:rate*100,
    knownSrsRedirectFailures:known.length,nominalLoginRedirects:nominal.length,unexpectedHttpOrScriptFailures:unexplained.length,
    postAverageMs:post.length?Math.round(post.reduce((s,r)=>s+(+r.elapsed),0)/post.length):null,
    postP95Ms:p95,postMaxMs:post.length?Math.max(...post.map(r=>+r.elapsed)):null,
    httpThroughputPerSecond:http.length/duration,finalMinutePostCount:finalMinute.length,
    finalMinutePostP95Ms:quantile(finalMinute.map(r=>+r.elapsed),.95),
    exploratoryPerformanceVerdict:issues.length?'INCONCLUSIVE':meets?'MEETS_PROPOSED_CRITERIA':'EXCEEDS_PROPOSED_LIMIT',
    functionalVerdict:known.length||unexplained.length||transport.length?'FAILED_OR_INCOMPLETE':'REDIRECT_ASSERTION_PASSED_ONLY',
    issues,criteria:'Exploratory only: transport errors <=1%; POST p95 <=10000 ms; NOT an approved SRS threshold',
    limitation:'Redirect does not prove DB creation. Reconcile account and profile counts. Timeouts do not prove server crash.'};
  fs.writeFileSync(path.join(dir,'summary.json'),JSON.stringify(result,null,2));
  console.log(JSON.stringify(result,null,2));return result;
}
function run(users,run,phoneBase) {
  users=Number(users);
  if(![5,6,7,8,9,10].includes(users))throw Error('Users must be 5, 6, 7, 8, 9 or 10');
  if(!/^[a-zA-Z0-9_-]{1,32}$/.test(run||''))throw Error('Supply a new run ID, e.g. cap5-01');
  if(!/^0\d{9}$/.test(phoneBase||''))throw Error('Supply an unused 10-digit starting phone number beginning with 0');
  const count=1000,first=Number(phoneBase),last=first+count-1;
  if(last>999999999)throw Error('Phone range would no longer begin with 0');
  const dir=path.join(root,'runs',run);
  if(fs.existsSync(dir))throw Error('Run already exists. Keep evidence; use a NEW run ID and phone range');
  const registry=path.join(root,'reserved-ranges.json');
  const used=fs.existsSync(registry)?JSON.parse(fs.readFileSync(registry,'utf8')):[];
  if(used.some(x=>first<=x.last&&last>=x.first))throw Error('Phone range overlaps a previously reserved range in this package');
  const java=process.env.CAP_JAVA || 'C:\\Program Files\\Eclipse Adoptium\\jdk-17.0.14.7-hotspot\\bin\\java.exe';
  const jar=process.env.CAP_JMETER_JAR || 'D:\\trích lỏ\\apache-jmeter-5.6.3\\bin\\ApacheJMeter.jar';
  if(!fs.existsSync(java)||!fs.existsSync(jar))throw Error('Java/JMeter path missing. Set CAP_JAVA and CAP_JMETER_JAR');
  const version=spawnSync(java,['-version'],{encoding:'utf8'});
  if(version.status!==0||!/version "17\./.test(version.stderr+version.stdout))throw Error('This package requires launching JMeter with Java 17');
  fs.mkdirSync(dir,{recursive:true});
  const batch='cap_'+crypto.randomBytes(6).toString('hex');
  const header='ten_dang_nhap,ten_khach_hang,email,so_dien_thoai,quan_huyen,xa_phuong,dia_chi_chi_tiet,mat_khau';
  const lines=Array.from({length:count},(_,i)=>[`${batch}_${i}`, 'Nguyễn Đức Trọng',`${batch}_${i}@example.com`,String(first+i).padStart(10,'0'),'Hà Đông','Yên Nghĩa','Xuân Mai Complex','123456'].join(','));
  fs.writeFileSync(path.join(dir,'data.csv'),header+'\n'+lines.join('\n')+'\n');
  const meta={test:'JM-REG-003-CAP',run,users,batch,rows:count,firstPhone:phoneBase,lastPhone:String(last).padStart(10,'0'),rampSeconds:30,durationSeconds:180,pauseMs:1000,responseTimeoutMs:15000,createdAt:new Date().toISOString()};
  fs.writeFileSync(path.join(dir,'run.json'),JSON.stringify(meta,null,2));
  used.push({run,first,last});fs.writeFileSync(registry,JSON.stringify(used,null,2));
  const args=['-Xms512m','-Xmx1g','-jar',jar,'-n','-t',path.join(root,'JM-REG-003-CAP.jmx'),'-l',path.join(dir,'results.jtl'),'-j',path.join(dir,'jmeter.log'),`-Jthreads=${users}`,`-Jdata=${path.join(dir,'data.csv')}`,
    '-Jjmeter.save.saveservice.output_format=csv','-Jjmeter.save.saveservice.print_field_names=true','-Jjmeter.save.saveservice.assertion_results_failure_message=true','-Jjmeter.save.saveservice.thread_counts=true',
    '-Jjmeter.save.saveservice.timestamp_format=ms','-Jsample_variables=ten_dang_nhap,redirect_path'];
  console.log('Run '+run+': '+users+' users, 180 seconds including 30-second ramp. Do not start another load run.');
  const child=spawnSync(java,args,{stdio:'inherit',cwd:root});
  if(child.error)throw child.error;
  if(child.status!==0)throw Error('JMeter exited with code '+child.status+'; keep logs and mark run incomplete');
  analyze(dir);
}
if(require.main===module) {
  try {
    const [cmd,a,b,c]=process.argv.slice(2);
    if(cmd==='run')run(a,b,c);
    else if(cmd==='analyze')analyze(path.resolve(a));
    else console.log('run-capacity.cmd run 5 cap5-01 0900100000\nrun-capacity.cmd analyze runs\\cap5-01');
  }catch(e){console.error('STOP: '+e.message);process.exitCode=1;}
}
module.exports={csvParse,analyze,quantile};
