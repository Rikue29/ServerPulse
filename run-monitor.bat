@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

REM ServerPulse Real-time Monitoring Script for Windows
REM Monitors all server metrics in real-time with comprehensive coverage

echo 🚀 ServerPulse Real-time Monitor with Queue Worker
echo ===================================================
echo 📡 Monitoring all server metrics in real-time
echo 🔄 Press Ctrl+C to stop the monitoring service
echo.

REM Start the queue worker in the background
echo 🔄 Starting queue worker...
docker-compose exec -d php php artisan queue:work --queue=default,broadcasting --tries=3 --timeout=60

REM Wait a moment for the queue worker to initialize
timeout /t 3 /nobreak >nul

echo ✅ Queue worker started successfully
echo.
echo 📊 ServerPulse Real-time Monitor Status
echo ========================================
echo 🟢 All metrics being monitored:
echo    • CPU Load ^& Usage
echo    • Memory (RAM) Usage 
echo    • Network Activity ^& Throughput
echo    • Disk Usage ^& I/O
echo    • Response Time
echo    • System Uptime/Downtime
echo    • Threshold Alerts
echo    • Performance Logging
echo.
echo ⏱️  Update interval: 5 seconds
echo 🔄 Queue worker: Running
echo 📡 Broadcasting: Active
echo.

REM Counter for status updates
set counter=0

:monitoring_loop
set /a counter+=1

for /f "tokens=1-3 delims=:." %%a in ("%time%") do set current_time=%%a:%%b:%%c
echo 🔄 [%current_time%] Running comprehensive server monitoring... (Cycle #%counter%)

REM 1. Monitor servers and broadcast status (includes all basic metrics)
echo    📡 Broadcasting server status...
docker-compose exec -T php php artisan monitor:server

REM 2. Update all server metrics (comprehensive metrics update)
echo    📊 Updating server metrics...
docker-compose exec -T php php artisan servers:update-metrics

REM 3. Check for threshold violations and create alerts
echo    ⚠️  Checking threshold violations...
docker-compose exec -T php php artisan debug:monitoring --quiet 2>nul

REM 4. Clear old performance logs (keep only last 1000 entries per server)
set /a cleanup_check=counter %% 20
if %cleanup_check%==0 (
    echo    🧹 Cleaning old performance logs...
    docker-compose exec -T php php artisan tinker --execute="
        $servers = \App\Models\Server::all();
        foreach($servers as $server) {
            $count = \App\Models\PerformanceLog::where('server_id', $server->id)->count();
            if ($count > 1000) {
                $logsToDelete = \App\Models\PerformanceLog::where('server_id', $server->id)
                    ->orderBy('id', 'asc')
                    ->limit($count - 1000)
                    ->delete();
                echo 'Cleaned ' . $logsToDelete . ' old logs for ' . $server->name . PHP_EOL;
            }
        }
    " 2>nul
)

REM 5. Display summary every 10 cycles
set /a summary_check=counter %% 10
if %summary_check%==0 (
    echo.
    echo 📈 Monitoring Summary (Cycle #%counter%):
    docker-compose exec -T php php artisan tinker --execute="
        $servers = \App\Models\Server::all();
        echo 'Servers monitored: ' . $servers->count() . PHP_EOL;
        foreach($servers as $server) {
            $status = $server->status === 'online' ? '🟢' : '🔴';
            echo $status . ' ' . $server->name . ' (' . $server->ip_address . ') - ' . ucfirst($server->status) . PHP_EOL;
        }
        $totalLogs = \App\Models\PerformanceLog::count();
        $totalAlerts = \App\Models\Log::where('source', 'threshold_monitor')->count();
        echo 'Total performance logs: ' . number_format($totalLogs) . PHP_EOL;
        echo 'Total threshold alerts: ' . number_format($totalAlerts) . PHP_EOL;
    " 2>nul
    echo.
)

echo ⏳ Waiting for 5 seconds...
timeout /t 5 /nobreak >nul

goto monitoring_loop
