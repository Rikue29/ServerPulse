@echo off
title ServerPulse Monitoring
echo Starting ServerPulse Monitoring...
echo ================================
echo.
echo Monitoring your Ubuntu VM: 192.168.159.128
echo Logs available at: http://127.0.0.1:8000/logs
echo.
echo Press Ctrl+C to stop monitoring
echo.

:loop
echo [%date% %time%] Running monitoring check...
cd /d "c:\laragon\www\ServerPulse"
php artisan monitor:server
if errorlevel 1 (
    echo ERROR: Monitoring failed!
    pause
) else (
    echo Monitoring completed successfully
)
echo.
timeout /t 30 /nobreak >nul
goto loop
