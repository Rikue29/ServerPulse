@echo off
title ServerPulse Auto-Monitor
echo ServerPulse Real-time Monitor Started
echo ======================================
echo Press Ctrl+C to stop the monitoring service
echo.

:loop
echo Running server monitoring directly...
cd /d C:\laragon\www\ServerPulse
php artisan monitor:server
php artisan servers:update-metrics

echo Waiting for 15 seconds...
timeout /t 15 /nobreak > nul
goto loop
