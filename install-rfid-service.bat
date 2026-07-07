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
    echo Right-click install-rfid-service.bat and select "Run as administrator"
    echo Do NOT run from an already-open cmd window unless it is elevated.
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

echo ERROR: PHP not found. Open Laragon once so PHP is available, then retry.
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
    powershell -NoProfile -ExecutionPolicy Bypass -Command "try { Invoke-WebRequest -Uri 'https://nssm.cc/release/nssm-2.24.zip' -OutFile '%PROJECT_DIR%\nssm.zip' -UseBasicParsing; exit 0 } catch { Write-Host $_.Exception.Message; exit 1 }"
    if %errorLevel% neq 0 (
        echo.
        echo ERROR: Could not download NSSM. Check internet connection and retry.
        echo Or download manually from https://nssm.cc/download
        echo Place nssm.exe in: %PROJECT_DIR%
        echo.
        pause
        exit /b 1
    )

    powershell -NoProfile -ExecutionPolicy Bypass -Command "Expand-Archive -Path '%PROJECT_DIR%\nssm.zip' -DestinationPath '%PROJECT_DIR%' -Force"
    if not exist "%PROJECT_DIR%\nssm-2.24\win64\nssm.exe" (
        echo ERROR: NSSM zip did not extract correctly.
        pause
        exit /b 1
    )

    copy /y "%PROJECT_DIR%\nssm-2.24\win64\nssm.exe" "%PROJECT_DIR%\nssm.exe" >nul
    rmdir /s /q "%PROJECT_DIR%\nssm-2.24"
    del "%PROJECT_DIR%\nssm.zip"
    echo NSSM downloaded successfully!
    echo.
)

if not exist "%PROJECT_DIR%\nssm.exe" (
    echo ERROR: nssm.exe is missing at %PROJECT_DIR%\nssm.exe
    pause
    exit /b 1
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
if %errorLevel% neq 0 (
    echo ERROR: nssm install failed.
    pause
    exit /b 1
)

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
if %errorLevel% neq 0 (
    echo WARNING: Service created but start failed. Check error log:
    echo %PROJECT_DIR%\writable\logs\rfid-listener-error.log
    echo.
)

echo.
echo ================================================
echo   Installation finished
echo ================================================
echo.
"%NSSM%" status RFIDListener
echo.
echo Startup Type: Automatic (starts on Windows boot)
echo Command:      php spark rfid:listen-all
echo.
echo Manage the service with rfid-service.bat (no admin needed):
echo   rfid-service.bat status
echo   rfid-service.bat log
echo   rfid-service.bat restart
echo.
echo Or use full path to nssm:
echo   "%NSSM%" status RFIDListener
echo.
pause
