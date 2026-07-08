@echo off
setlocal EnableDelayedExpansion

echo ================================================
echo   Install Analytics API as Windows Service
echo ================================================
echo.

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator!
    echo Right-click install-analytics-service.bat and select "Run as administrator"
    echo.
    pause
    exit /b 1
)

cd /d "%~dp0"
set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"

set "PYTHON_EXE="

where py >nul 2>&1
if %errorLevel% equ 0 (
    for /f "delims=" %%i in ('where py 2^>nul') do (
        set "PY_LAUNCHER=%%i"
        goto py_found
    )
)

where python >nul 2>&1
if %errorLevel% equ 0 (
    for /f "delims=" %%i in ('where python 2^>nul') do (
        echo %%i | findstr /i "WindowsApps" >nul
        if !errorLevel! neq 0 (
            set "PYTHON_EXE=%%i"
            goto python_found
        )
    )
)

if exist "C:\Python312\python.exe" set "PYTHON_EXE=C:\Python312\python.exe" & goto python_found
if exist "C:\Program Files\Python312\python.exe" set "PYTHON_EXE=C:\Program Files\Python312\python.exe" & goto python_found
if exist "%LOCALAPPDATA%\Programs\Python\Python312\python.exe" set "PYTHON_EXE=%LOCALAPPDATA%\Programs\Python\Python312\python.exe" & goto python_found
if exist "%LOCALAPPDATA%\Python\bin\python.exe" set "PYTHON_EXE=%LOCALAPPDATA%\Python\bin\python.exe" & goto python_found

echo ERROR: Python not found.
echo.
echo Install Python 3.12 from https://www.python.org/downloads/
echo During install, check "Add python.exe to PATH".
echo Then re-run this script.
echo.
pause
exit /b 1

:py_found
echo Project: %PROJECT_DIR%
echo Python:  %PY_LAUNCHER%
echo.

echo Installing Python dependencies...
"%PY_LAUNCHER%" -m pip install --upgrade pip
"%PY_LAUNCHER%" -m pip install -r "%PROJECT_DIR%\requirements.txt"
if %errorLevel% neq 0 (
    echo ERROR: pip install failed.
    pause
    exit /b 1
)

for /f "delims=" %%v in ('"%PY_LAUNCHER%" -c "import sys; print(sys.executable)"') do set "PYTHON_EXE=%%v"
echo Using: %PYTHON_EXE%
echo.

if not exist "%PROJECT_DIR%\writable\logs" mkdir "%PROJECT_DIR%\writable\logs"

if not exist "%PROJECT_DIR%\nssm.exe" (
    echo Downloading NSSM...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "try { Invoke-WebRequest -Uri 'https://nssm.cc/release/nssm-2.24.zip' -OutFile '%PROJECT_DIR%\nssm.zip' -UseBasicParsing; exit 0 } catch { Write-Host $_.Exception.Message; exit 1 }"
    if %errorLevel% neq 0 (
        echo ERROR: Could not download NSSM. Place nssm.exe in: %PROJECT_DIR%
        pause
        exit /b 1
    )
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Expand-Archive -Path '%PROJECT_DIR%\nssm.zip' -DestinationPath '%PROJECT_DIR%' -Force"
    copy /y "%PROJECT_DIR%\nssm-2.24\win64\nssm.exe" "%PROJECT_DIR%\nssm.exe" >nul
    rmdir /s /q "%PROJECT_DIR%\nssm-2.24"
    del "%PROJECT_DIR%\nssm.zip"
)

set "NSSM=%PROJECT_DIR%\nssm.exe"

"%NSSM%" status AnalyticsAPI >nul 2>&1
if %errorLevel% equ 0 (
    echo Existing AnalyticsAPI service found — updating...
    "%NSSM%" stop AnalyticsAPI >nul 2>&1
    "%NSSM%" remove AnalyticsAPI confirm >nul 2>&1
)

echo Creating Windows Service...
"%NSSM%" install AnalyticsAPI "%PYTHON_EXE%" "analytics_api.py"
if %errorLevel% neq 0 (
    echo ERROR: nssm install failed.
    pause
    exit /b 1
)

"%NSSM%" set AnalyticsAPI AppDirectory "%PROJECT_DIR%"
"%NSSM%" set AnalyticsAPI DisplayName "WorkWise Analytics API"
"%NSSM%" set AnalyticsAPI Description "AI analytics backend for Analytics Assistant (port 8001)"
"%NSSM%" set AnalyticsAPI Start SERVICE_AUTO_START
"%NSSM%" set AnalyticsAPI AppStdout "%PROJECT_DIR%\writable\logs\analytics-api.log"
"%NSSM%" set AnalyticsAPI AppStderr "%PROJECT_DIR%\writable\logs\analytics-api-error.log"
"%NSSM%" set AnalyticsAPI AppRotateFiles 1
"%NSSM%" set AnalyticsAPI AppRotateOnline 1
"%NSSM%" set AnalyticsAPI AppRotateBytes 1048576

echo.
echo Starting service...
"%NSSM%" start AnalyticsAPI
if %errorLevel% neq 0 (
    echo WARNING: Service created but start failed. Check:
    echo %PROJECT_DIR%\writable\logs\analytics-api-error.log
    echo.
)

echo.
echo ================================================
echo   Installation finished
echo ================================================
echo.
"%NSSM%" status AnalyticsAPI
echo.
echo API:    http://localhost:8001
echo Docs:   http://localhost:8001/docs
echo Manage: analytics-service.bat status ^| restart ^| log
echo.
pause
