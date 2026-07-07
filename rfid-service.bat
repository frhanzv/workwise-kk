@echo off
setlocal
cd /d "%~dp0"

set "NSSM=%~dp0nssm.exe"
set "LOG=%~dp0writable\logs\rfid-listener.log"
set "ERR=%~dp0writable\logs\rfid-listener-error.log"

if not exist "%NSSM%" (
    echo ERROR: nssm.exe not found in this folder.
    echo Run install-rfid-service.bat as Administrator first.
    echo.
    pause
    exit /b 1
)

if "%1"=="" goto usage

if /i "%1"=="status"  goto do_status
if /i "%1"=="start"   goto do_start
if /i "%1"=="stop"    goto do_stop
if /i "%1"=="restart" goto do_restart
if /i "%1"=="log"     goto do_log
if /i "%1"=="error"   goto do_error
goto usage

:do_status
"%NSSM%" status RFIDListener
exit /b %errorLevel%

:do_start
"%NSSM%" start RFIDListener
exit /b %errorLevel%

:do_stop
"%NSSM%" stop RFIDListener
exit /b %errorLevel%

:do_restart
"%NSSM%" stop RFIDListener
timeout /t 2 /nobreak >nul
"%NSSM%" start RFIDListener
exit /b %errorLevel%

:do_log
if not exist "%LOG%" (
    echo Log not found: %LOG%
    exit /b 1
)
type "%LOG%"
exit /b 0

:do_error
if not exist "%ERR%" (
    echo Error log not found: %ERR%
    exit /b 1
)
type "%ERR%"
exit /b 0

:usage
echo RFID Listener service helper
echo.
echo Usage: rfid-service.bat [command]
echo.
echo   status   - show service status
echo   start    - start service
echo   stop     - stop service
echo   restart  - restart service
echo   log      - show rfid-listener.log
echo   error    - show rfid-listener-error.log
echo.
echo NSSM path: %NSSM%
exit /b 0
