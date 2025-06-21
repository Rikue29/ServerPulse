@echo off
title ServerPulse Auto-Monitor with Queue Worker
echo ServerPulse Real-time Monitor Started with Queue Worker
echo ===================================================
echo Press Ctrl+C to stop the monitoring service
echo.

REM First, start the queue worker in a separate window
start cmd /k "cd /d C:\laragon\www\ServerPulse && php artisan queue:work --queue=default,broadcasting --tries=3"
echo Queue worker started in a separate window.
echo.

REM Wait a moment for the queue worker to initialize
timeout /t 2 /nobreak > nul

:loop
echo Running server monitoring directly...
cd /d C:\laragon\www\ServerPulse
php artisan monitor:server
php artisan servers:update-metrics

echo Waiting for 2 seconds...
timeout /t 2 /nobreak > nul
goto loop
