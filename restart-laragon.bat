@echo off
echo Restarting Laragon Services for ServerPulse...
echo.

echo Stopping Laragon services...
taskkill /F /IM nginx.exe 2>nul
taskkill /F /IM php-cgi.exe 2>nul

echo.
echo Starting Nginx...
start "" "C:\laragon\bin\nginx\nginx-1.27.3\nginx.exe"

echo.
echo Checking if services are running...
timeout /t 2 /nobreak >nul

echo.
echo Testing ServerPulse on port 8080...
curl -I http://localhost:8080 2>nul || echo Could not connect to ServerPulse

echo.
echo Services restarted! ServerPulse should be available at:
echo - Local: http://localhost:8080
echo - Network: http://192.168.0.101:8080
echo.
pause
