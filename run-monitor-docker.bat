@echo off
title ServerPulse Auto-Monitor (Docker)
echo ServerPulse Real-time Monitor Started (Docker)
echo ======================================
echo Press Ctrl+C to stop the monitoring service

:loop
echo %date% %time% - Starting monitoring cycle...

:: First, update the metrics in the database
echo %date% %time% - Running update-metrics command...
docker exec -it php php artisan servers:update-metrics
echo %date% %time% - Update-metrics completed.

:: Wait 1 second to allow the database to settle
timeout /t 1 /nobreak > nul

:: Then broadcast the updated metrics
echo %date% %time% - Running monitor:server command...
docker exec -it php php artisan monitor:server
echo %date% %time% - Monitor:server completed.

:: Calculate when to do the next update to ensure we don't overlap cycles
echo %date% %time% - Monitoring cycle completed. Waiting 10 seconds before next cycle...
timeout /t 10 /nobreak > nul
goto loop 