#!/bin/bash

# ServerPulse Real-time Monitoring Script
# Monitors all server metrics in real-time with comprehensive coverage

# Function to cleanup on script exit
cleanup() {
    echo ""
    echo "🛑 Stopping ServerPulse monitoring service..."
    echo "Clearing queues..."
    docker-compose exec php php artisan queue:clear
    docker-compose exec php php artisan queue:flush
    echo "✅ Monitoring service stopped gracefully"
    exit 0
}

# Function to display status
show_status() {
    echo ""
    echo "📊 ServerPulse Real-time Monitor Status"
    echo "========================================"
    echo "🟢 All metrics being monitored:"
    echo "   • CPU Load & Usage"
    echo "   • Memory (RAM) Usage" 
    echo "   • Network Activity & Throughput"
    echo "   • Disk Usage & I/O"
    echo "   • Response Time"
    echo "   • System Uptime/Downtime"
    echo "   • Threshold Alerts"
    echo "   • Performance Logging"
    echo ""
    echo "⏱️  Update interval: 5 seconds"
    echo "🔄 Queue worker: Running"
    echo "📡 Broadcasting: Active"
    echo ""
}

# Register the cleanup function to run on script termination
trap cleanup SIGINT SIGTERM

echo "🚀 ServerPulse Real-time Monitor with Queue Worker"
echo "==================================================="
echo "📡 Monitoring all server metrics in real-time"
echo "🔄 Press Ctrl+C to stop the monitoring service"
echo ""

# Start the queue worker in the background
echo "🔄 Starting queue worker..."
docker-compose exec -d php php artisan queue:work --queue=default,broadcasting --tries=3 --timeout=60

# Wait a moment for the queue worker to initialize
sleep 3

# Check if queue worker started successfully
if docker-compose exec php php artisan queue:monitor --queue=default | grep -q "running"; then
    echo "✅ Queue worker started successfully"
else
    echo "⚠️  Queue worker may not be running properly"
fi

# Display initial status
show_status

# Counter for status updates
counter=0

# Main monitoring loop
while true; do
    counter=$((counter + 1))
    
    echo "🔄 [$(date '+%H:%M:%S')] Running comprehensive server monitoring... (Cycle #$counter)"
    
    # 1. Monitor servers and broadcast status (includes all basic metrics)
    echo "   📡 Broadcasting server status..."
    docker-compose exec -T php php artisan monitor:server
    
    # 2. Update all server metrics (comprehensive metrics update)
    echo "   📊 Updating server metrics..."
    docker-compose exec -T php php artisan servers:update-metrics
    
    # 3. Check for threshold violations and create alerts
    echo "   ⚠️  Checking threshold violations..."
    docker-compose exec -T php php artisan debug:monitoring --quiet 2>/dev/null || true
    
    # 4. Clear old performance logs (keep only last 1000 entries per server)
    if [ $((counter % 20)) -eq 0 ]; then
        echo "   🧹 Cleaning old performance logs..."
        docker-compose exec -T php php artisan tinker --execute="
            \$servers = \App\Models\Server::all();
            foreach(\$servers as \$server) {
                \$count = \App\Models\PerformanceLog::where('server_id', \$server->id)->count();
                if (\$count > 1000) {
                    \$logsToDelete = \App\Models\PerformanceLog::where('server_id', \$server->id)
                        ->orderBy('id', 'asc')
                        ->limit(\$count - 1000)
                        ->delete();
                    echo 'Cleaned ' . \$logsToDelete . ' old logs for ' . \$server->name . PHP_EOL;
                }
            }
        " 2>/dev/null || true
    fi
    
    # 5. Display summary every 10 cycles
    if [ $((counter % 10)) -eq 0 ]; then
        echo ""
        echo "📈 Monitoring Summary (Cycle #$counter):"
        docker-compose exec -T php php artisan tinker --execute="
            \$servers = \App\Models\Server::all();
            echo 'Servers monitored: ' . \$servers->count() . PHP_EOL;
            foreach(\$servers as \$server) {
                \$status = \$server->status === 'online' ? '🟢' : '🔴';
                echo \$status . ' ' . \$server->name . ' (' . \$server->ip_address . ') - ' . ucfirst(\$server->status) . PHP_EOL;
            }
            \$totalLogs = \App\Models\PerformanceLog::count();
            \$totalAlerts = \App\Models\Log::where('source', 'threshold_monitor')->count();
            echo 'Total performance logs: ' . number_format(\$totalLogs) . PHP_EOL;
            echo 'Total threshold alerts: ' . number_format(\$totalAlerts) . PHP_EOL;
        " 2>/dev/null || echo "   Unable to display summary"
        echo ""
    fi
    
    echo "⏳ Waiting for 5 seconds..."
    sleep 5
done
