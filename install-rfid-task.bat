@echo off
setlocal EnableDelayedExpansion

echo ================================================
echo   Install RFID Listener (Task Scheduler)
echo   No NSSM required
echo ================================================
echo.

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Run as Administrator.
    pause
    exit /b 1
)

cd /d "%~dp0"
set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"

set "PHP_EXE="
where php >nul 2>&1 && for /f "delims=" %%i in ('where php 2^>nul') do set "PHP_EXE=%%i" & goto php_ok
for /f "delims=" %%d in ('dir /b /ad /o-n "C:\laragon\bin\php\php-*" 2^>nul') do (
    if exist "C:\laragon\bin\php\%%d\php.exe" set "PHP_EXE=C:\laragon\bin\php\%%d\php.exe" & goto php_ok
)
echo ERROR: PHP not found.
pause
exit /b 1

:php_ok
if not exist "%PROJECT_DIR%\writable\logs" mkdir "%PROJECT_DIR%\writable\logs"

set "TASK_NAME=WorkWise RFID Listener"
set "TASK_CMD=cmd /c cd /d \"%PROJECT_DIR%\" ^&^& \"%PHP_EXE%\" spark rfid:listen-all ^>^> \"%PROJECT_DIR%\writable\logs\rfid-listener.log\" 2^>^> \"%PROJECT_DIR%\writable\logs\rfid-listener-error.log\""

echo Project: %PROJECT_DIR%
echo PHP:     %PHP_EXE%
echo Task:    %TASK_NAME%
echo.

schtasks /query /tn "%TASK_NAME%" >nul 2>&1
if %errorLevel% equ 0 (
    echo Removing old scheduled task...
    schtasks /delete /tn "%TASK_NAME%" /f >nul
)

echo Creating scheduled task (runs at Windows startup)...
schtasks /create /tn "%TASK_NAME%" /sc onstart /ru SYSTEM /rl HIGHEST /f /tr "%TASK_CMD%"
if %errorLevel% neq 0 (
    echo ERROR: Could not create scheduled task.
    pause
    exit /b 1
)

echo Starting task now...
schtasks /run /tn "%TASK_NAME%"

echo.
echo ================================================
echo   Task installed
echo ================================================
echo.
echo Starts automatically on every Windows boot.
echo.
echo Manage:
echo   schtasks /query /tn "%TASK_NAME%"
echo   schtasks /run /tn "%TASK_NAME%"
echo   schtasks /end /tn "%TASK_NAME%"
echo   schtasks /delete /tn "%TASK_NAME%" /f
echo.
echo Log: %PROJECT_DIR%\writable\logs\rfid-listener.log
echo.
pause
