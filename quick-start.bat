@echo off
echo ==================================================
echo         ServerPulse - Quick Start Script
echo ==================================================
echo.

echo [1/4] Starting Laravel development server...
start /B php artisan serve --host=127.0.0.1 --port=8000
timeout /t 3 >nul

echo [2/4] Running initial database migrations...
php artisan migrate --force

echo [3/4] Creating test server (if not exists)...
php artisan create:test-server

echo [4/4] Running initial monitoring check...
php artisan monitor:server

echo.
echo ==================================================
echo             Setup Complete! 
echo ==================================================
echo.
echo Application is running at: http://127.0.0.1:8000
echo.
echo Available Features:
echo  - Dashboard: Server overview and management
echo  - Logs: Modern interface for monitoring alerts
echo  - Add Server: Create new monitored servers
echo.
echo Quick Commands:
echo  - Run monitoring: php artisan monitor:server
echo  - Debug monitoring: php artisan debug:monitoring
echo  - Continuous monitoring: run-continuous-monitoring.bat
echo.
echo Press any key to open the application in your browser...
pause >nul
start http://127.0.0.1:8000
echo.
echo Happy monitoring! 🚀
