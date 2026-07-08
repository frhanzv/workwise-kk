@echo off
title WorkWise Analytics API (port 8001)
cd /d "%~dp0"

where py >nul 2>&1
if %errorLevel% equ 0 (
    set "PY=py"
    goto run
)

where python >nul 2>&1
if %errorLevel% equ 0 (
    set "PY=python"
    goto run
)

echo ERROR: Python not found. Install from https://www.python.org/downloads/
pause
exit /b 1

:run
echo Starting Analytics API on http://localhost:8001
echo Press Ctrl+C to stop.
echo.

:start
%PY% analytics_api.py

echo.
echo API stopped. Restarting in 5 seconds...
echo Press Ctrl+C to exit permanently.
timeout /t 5

goto start
