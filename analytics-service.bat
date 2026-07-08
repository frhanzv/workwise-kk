@echo off
setlocal
cd /d "%~dp0"

set "NSSM=%~dp0nssm.exe"
set "LOG=%~dp0writable\logs\analytics-api.log"
set "ERR=%~dp0writable\logs\analytics-api-error.log"

if not exist "%NSSM%" (
    echo ERROR: nssm.exe not found in this folder.
    echo Right-click install-analytics-service.bat ^> Run as administrator
    pause
    exit /b 1
)

sc.exe query AnalyticsAPI >nul 2>&1
if %errorLevel% neq 0 (
    echo AnalyticsAPI service is NOT installed yet.
    echo Right-click install-analytics-service.bat ^> Run as administrator
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
"%NSSM%" status AnalyticsAPI
exit /b %errorLevel%

:do_start
"%NSSM%" start AnalyticsAPI
exit /b %errorLevel%

:do_stop
"%NSSM%" stop AnalyticsAPI
exit /b %errorLevel%

:do_restart
"%NSSM%" stop AnalyticsAPI
timeout /t 2 /nobreak >nul
"%NSSM%" start AnalyticsAPI
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
echo Analytics API service helper
echo.
echo Usage: analytics-service.bat [command]
echo.
echo   status   - show service status
echo   start    - start service
echo   stop     - stop service
echo   restart  - restart service
echo   log      - show analytics-api.log
echo   error    - show analytics-api-error.log
echo.
exit /b 0
