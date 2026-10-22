@echo off
setlocal
cd /d "%~dp0"
node capacity.cjs %*
exit /b %errorlevel%
