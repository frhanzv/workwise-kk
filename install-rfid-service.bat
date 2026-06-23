@echo off
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

echo Installing RFID Listener service...
echo.

REM Download NSSM if not exists
if not exist "nssm.exe" (
    echo Downloading NSSM (Non-Sucking Service Manager)...
    powershell -Command "Invoke-WebRequest -Uri 'https://nssm.cc/release/nssm-2.24.zip' -OutFile 'nssm.zip'"
    powershell -Command "Expand-Archive -Path 'nssm.zip' -DestinationPath '.' -Force"
    copy "nssm-2.24\win64\nssm.exe" "nssm.exe"
    rmdir /s /q nssm-2.24
    del nssm.zip
    echo NSSM downloaded successfully!
    echo.
)

REM Install the service
echo Creating Windows Service...
nssm install RFIDListener "C:\laragon\bin\php\php-8.3.20-Win32-vs16-x64\php.exe" "spark rfid:listen-all"
nssm set RFIDListener AppDirectory "C:\laragon\www\workwise"
nssm set RFIDListener DisplayName "WorkWise RFID Listener"
nssm set RFIDListener Description "Listens to Yanzeo SA810 RFID reader for automatic attendance tracking"
nssm set RFIDListener Start SERVICE_AUTO_START
nssm set RFIDListener AppStdout "C:\laragon\www\workwise\writable\logs\rfid-listener.log"
nssm set RFIDListener AppStderr "C:\laragon\www\workwise\writable\logs\rfid-listener-error.log"
nssm set RFIDListener AppRotateFiles 1
nssm set RFIDListener AppRotateOnline 1
nssm set RFIDListener AppRotateBytes 1048576

echo.
echo Starting service...
nssm start RFIDListener

echo.
echo ================================================
echo   Service installed successfully!
echo ================================================
echo.
echo Service Name: RFIDListener
echo Status: Running
echo Startup Type: Automatic
echo.
echo The RFID listener will now start automatically when Windows boots.
echo.
echo Useful commands:
echo   - Check status: nssm status RFIDListener
echo   - Stop service: nssm stop RFIDListener
echo   - Start service: nssm start RFIDListener
echo   - Remove service: nssm remove RFIDListener confirm
echo.
echo Logs are saved to: writable\logs\rfid-listener.log
echo.
pause
