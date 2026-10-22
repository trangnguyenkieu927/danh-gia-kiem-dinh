const assert=require('node:assert/strict');
const fs=require('node:fs');
const os=require('node:os');
const path=require('node:path');
const {csvParse,quantile,analyze}=require('./capacity.cjs');
assert.deepEqual(csvParse('\uFEFFa,b\r\n"x,y","z""q"\r\n'),[['a','b'],['x,y','z"q']]);
assert.equal(quantile(Array.from({length:100},(_,i)=>i+1),.95),95);
const tmp=fs.mkdtempSync(path.join(os.tmpdir(),'capacity-selftest-'));
const old=console.log;console.log=()=>{};
try {
  fs.writeFileSync(path.join(tmp,'run.json'),JSON.stringify({run:'SYNTHETIC',users:5}));
  function fixture(mode) {
    const lines=['timeStamp,elapsed,label,responseCode,success,failureMessage,allThreads'];
    for(let i=0;i<90;i++) {
      lines.push(`${1000000+i*2000},100,REG GET form,200,true,,5`);
      const fail=mode==='timeout'&&i%2===0;
      lines.push(`${1000100+i*2000},${fail?15000:1500},REG POST strict SRS redirect,${fail?'Non HTTP response code: java.net.SocketTimeoutException':'302'},false,${fail?'UNEXPECTED_FLOW: timeout':mode==='validation'?'UNEXPECTED_FLOW: register':'SRS_REDIRECT: dashboard'},5`);
    }
    fs.writeFileSync(path.join(tmp,'results.jtl'),lines.join('\n'));
    return analyze(tmp);
  }
  const healthy=fixture('known');assert.equal(healthy.exploratoryPerformanceVerdict,'MEETS_PROPOSED_CRITERIA');assert.equal(healthy.knownSrsRedirectFailures,90);assert.equal(healthy.functionalVerdict,'FAILED_OR_INCOMPLETE');
  const timeout=fixture('timeout');assert.equal(timeout.exploratoryPerformanceVerdict,'EXCEEDS_PROPOSED_LIMIT');assert.equal(timeout.postP95Ms,15000);assert.equal(timeout.transportErrorPercent,25);
  assert.equal(fixture('validation').exploratoryPerformanceVerdict,'INCONCLUSIVE');
}finally{console.log=old;fs.rmSync(tmp,{recursive:true,force:true});}
console.log('PASS: CSV quoting/BOM, p95, separate SRS/transport, timeout and invalid-flow cases. Synthetic data only.');
