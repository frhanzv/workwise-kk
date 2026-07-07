@echo off
setlocal EnableDelayedExpansion

echo ================================================
echo   Install RFID Listener as Windows Service
echo ================================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator!
    echo Right-click this file and select "Run as administrator"
    echo.
    pause
    exit /b 1
)

cd /d "%~dp0"
set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"

set "PHP_EXE="
where php >nul 2>&1
if %errorLevel% equ 0 (
    for /f "delims=" %%i in ('where php 2^>nul') do (
        set "PHP_EXE=%%i"
        goto php_found
    )
)

if exist "C:\laragon\bin\php\" (
    for /f "delims=" %%d in ('dir /b /ad /o-n "C:\laragon\bin\php\php-*" 2^>nul') do (
        if exist "C:\laragon\bin\php\%%d\php.exe" (
            set "PHP_EXE=C:\laragon\bin\php\%%d\php.exe"
            goto php_found
        )
    )
)

echo ERROR: PHP not found. Add Laragon PHP to PATH or install Laragon.
echo.
pause
exit /b 1

:php_found
echo Project: %PROJECT_DIR%
echo PHP:     %PHP_EXE%
echo.

if not exist "%PROJECT_DIR%\writable\logs" mkdir "%PROJECT_DIR%\writable\logs"

echo Installing RFID Listener service...
echo.

REM Download NSSM if not exists
if not exist "%PROJECT_DIR%\nssm.exe" (
    echo Downloading NSSM (Non-Sucking Service Manager)...
    powershell -Command "Invoke-WebRequest -Uri 'https://nssm.cc/release/nssm-2.24.zip' -OutFile '%PROJECT_DIR%\nssm.zip'"
    powershell -Command "Expand-Archive -Path '%PROJECT_DIR%\nssm.zip' -DestinationPath '%PROJECT_DIR%' -Force"
    copy "%PROJECT_DIR%\nssm-2.24\win64\nssm.exe" "%PROJECT_DIR%\nssm.exe"
    rmdir /s /q "%PROJECT_DIR%\nssm-2.24"
    del "%PROJECT_DIR%\nssm.zip"
    echo NSSM downloaded successfully!
    echo.
)

set "NSSM=%PROJECT_DIR%\nssm.exe"

REM Remove existing service so re-run updates paths cleanly
"%NSSM%" status RFIDListener >nul 2>&1
if %errorLevel% equ 0 (
    echo Existing RFIDListener service found — updating...
    "%NSSM%" stop RFIDListener >nul 2>&1
    "%NSSM%" remove RFIDListener confirm >nul 2>&1
)

echo Creating Windows Service...
"%NSSM%" install RFIDListener "%PHP_EXE%" "spark rfid:listen-all"
"%NSSM%" set RFIDListener AppDirectory "%PROJECT_DIR%"
"%NSSM%" set RFIDListener DisplayName "WorkWise RFID Listener"
"%NSSM%" set RFIDListener Description "Listens to all zone RFID readers (rfid:listen-all)"
"%NSSM%" set RFIDListener Start SERVICE_AUTO_START
"%NSSM%" set RFIDListener AppStdout "%PROJECT_DIR%\writable\logs\rfid-listener.log"
"%NSSM%" set RFIDListener AppStderr "%PROJECT_DIR%\writable\logs\rfid-listener-error.log"
"%NSSM%" set RFIDListener AppRotateFiles 1
"%NSSM%" set RFIDListener AppRotateOnline 1
"%NSSM%" set RFIDListener AppRotateBytes 1048576

echo.
echo Starting service...
"%NSSM%" start RFIDListener

echo.
echo ================================================
echo   Service installed successfully!
echo ================================================
echo.
echo Service Name: RFIDListener
echo Startup Type: Automatic (starts on Windows boot)
echo Command:      php spark rfid:listen-all
echo.
echo Useful commands:
echo   - Check status: "%NSSM%" status RFIDListener
echo   - Stop service: "%NSSM%" stop RFIDListener
echo   - Start service: "%NSSM%" start RFIDListener
echo   - Remove service: "%NSSM%" remove RFIDListener confirm
echo.
echo Logs: %PROJECT_DIR%\writable\logs\rfid-listener.log
echo.
pause
