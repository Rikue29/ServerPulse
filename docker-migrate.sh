#!/bin/bash
# Run migrations inside Docker container
echo "Running database migrations in Docker container..."
docker-compose exec php php artisan migrate --force

# Run the monitor with Docker commands
echo "Starting server monitoring in Docker..."
bash run-monitor.sh
