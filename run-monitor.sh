#!/bin/bash

# Function to cleanup on script exit
cleanup() {
    echo "Stopping monitoring service..."
    docker-compose exec php php artisan queue:clear
    docker-compose exec php php artisan queue:flush
    exit 0
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
sleep 3

# Main monitoring loop
while true; do
    echo "Running server monitoring..."
    docker-compose exec -T php php artisan monitor:server
    docker-compose exec -T php php artisan servers:update-metrics
    
    echo "Waiting for 5 seconds..."
    sleep 5
done
