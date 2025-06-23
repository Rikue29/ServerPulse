#!/bin/bash

# Function to cleanup on script exit
cleanup() {
    echo "Stopping monitoring service..."
    docker-compose exec php php artisan queue:clear
    docker-compose exec php php artisan queue:flush
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
    echo "⏱️  Update interval: 2 seconds"
    echo "🔄 Queue worker: Running"
    echo "📡 Broadcasting: Active"
    echo ""
}

# Register the cleanup function to run on script termination
trap cleanup SIGINT SIGTERM

echo "ServerPulse Real-time Monitor with Queue Worker"
echo "==================================================="
echo "Press Ctrl+C to stop the monitoring service"
echo ""

# Start the queue worker in the background
echo "Starting queue worker..."
docker-compose exec -d php php artisan queue:work --queue=default,broadcasting --tries=3

# Wait a moment for the queue worker to initialize
sleep 2

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
    
    # 1. Monitor servers and broadcast status (includes all basic metrics)
    # More frequent updates for smoother real-time graphs
    docker-compose exec -T php php artisan monitor:server
    
    # Small delay to avoid overwhelming the browser
    sleep 0.5
    
    # 2. Update all server metrics (comprehensive metrics update)
    docker-compose exec -T php php artisan servers:update-metrics
    
    # 3. Check for threshold violations and create alerts
    docker-compose exec -T php php artisan debug:monitoring --quiet 2>/dev/null || true
    
    # 4. Clear old performance logs (keep only last 1000 entries per server)
    if [ $((counter % 50)) -eq 0 ]; then
        docker-compose exec -T php php artisan tinker --execute="
            \$servers = \App\Models\Server::all();
            foreach(\$servers as \$server) {
                \$count = \App\Models\PerformanceLog::where('server_id', \$server->id)->count();
                if (\$count > 1000) {
                    \$logsToDelete = \App\Models\PerformanceLog::where('server_id', \$server->id)
                        ->orderBy('id', 'asc')
                        ->limit(\$count - 1000)
                        ->delete();
                }
            }
        " 2>/dev/null || true
    fi
    
    # 5. Display summary every 25 cycles (every 50 seconds)
    if [ $((counter % 25)) -eq 0 ]; then
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
    
    sleep 2
done
