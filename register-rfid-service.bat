@echo off
setlocal EnableDelayedExpansion

echo ================================================
echo   Register RFID Listener Windows Service
echo ================================================
echo.

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Run this as Administrator.
    echo Right-click register-rfid-service.bat ^> Run as administrator
    echo.
    pause
    exit /b 1
)

cd /d "%~dp0"
set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"
set "NSSM=%PROJECT_DIR%\nssm.exe"

if not exist "%NSSM%" (
    echo ERROR: %NSSM% not found.
    echo Run install-rfid-service.bat first, or place nssm.exe in this folder.
    pause
    exit /b 1
)

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

echo Project: %PROJECT_DIR%
echo PHP:     %PHP_EXE%
echo NSSM:    %NSSM%
echo.

"%NSSM%" status RFIDListener >nul 2>&1
if %errorLevel% equ 0 (
    echo Stopping existing service...
    "%NSSM%" stop RFIDListener >nul 2>&1
    "%NSSM%" remove RFIDListener confirm >nul 2>&1
)

echo Installing service...
"%NSSM%" install RFIDListener "%PHP_EXE%" "spark rfid:listen-all"
if %errorLevel% neq 0 (
    echo ERROR: install failed.
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

echo Starting service...
"%NSSM%" start RFIDListener

echo.
"%NSSM%" status RFIDListener
echo.
echo Done. Check with: rfid-service.bat status
echo Log: %PROJECT_DIR%\writable\logs\rfid-listener.log
echo.
pause
