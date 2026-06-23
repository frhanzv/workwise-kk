@echo off
title RFID Reader Listener Service
cd /d "%~dp0"

:start
echo Starting RFID Reader Listener...
php spark rfid:listen

echo.
echo Service stopped. Restarting in 5 seconds...
echo Press Ctrl+C to exit permanently.
timeout /t 5

goto start
