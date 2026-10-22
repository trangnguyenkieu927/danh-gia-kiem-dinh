@echo off
setlocal
cd /d "%~dp0"
if "%~2"=="" goto usage
if not defined JMETER_BAT set "JMETER_BAT=jmeter.bat"
if not defined HOST set "HOST=127.0.0.1"
if not defined PORT set "PORT=8000"
if not defined THREADS set "THREADS=1"
if not defined LOOPS set "LOOPS=1"
if not defined RAMP set "RAMP=1"
if not defined DURATION set "DURATION=60"
if not defined PAUSE_MS set "PAUSE_MS=1000"
if not defined ON_ERROR set "ON_ERROR=stopthread"
if not defined DATA_FILE (echo Set DATA_FILE first.& exit /b 2)
if not exist "%DATA_FILE%" (echo CSV not found.& exit /b 2)
if not exist "%~1.jmx" (echo Unknown scenario.& exit /b 2)
if exist "results\%~2" (echo Run ID exists. Choose a new ID.& exit /b 2)
mkdir "results\%~2"
call "%JMETER_BAT%" -n -t "%~1.jmx" -l "results\%~2\results.jtl" -j "results\%~2\jmeter.log" -e -o "results\%~2\html" -Jhost=%HOST% -Jport=%PORT% -Jthreads=%THREADS% -Jloops=%LOOPS% -Jramp=%RAMP% -Jduration=%DURATION% -Jpause_ms=%PAUSE_MS% -Jon_error=%ON_ERROR% "-Jdata=%DATA_FILE%" -Jjmeter.save.saveservice.assertion_results_failure_message=true
exit /b %errorlevel%
:usage
echo Usage: run.cmd JM-AUTH-001 auth-smoke-01
exit /b 2
