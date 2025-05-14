@echo off
title ServerPulse Quick Monitor Test
cd /d C:\laragon\www\ServerPulse

echo Testing monitor commands...
echo Running monitor:server command:
php artisan monitor:server

echo.
echo Running servers:update-metrics command:
php artisan servers:update-metrics

echo.
echo Test complete! Both commands executed successfully.
echo.
echo If you saw server data being broadcasted above, the monitoring is working correctly.
echo You can now run the service installation or use run-monitor.bat for continuous monitoring.

pause
