#!/bin/bash
# Rebuild frontend assets first for real-time graph improvements
echo "Building frontend assets for improved real-time graphs..."
npm run build

# Clear application cache to ensure all changes are applied
echo "Clearing application cache..."
docker-compose exec php php artisan optimize:clear

# Run migrations inside Docker container
echo "Running database migrations in Docker container..."
docker-compose exec php php artisan migrate --force

# Restart queue workers for improved real-time processing
echo "Restarting queue workers..."
docker-compose exec php php artisan queue:restart

# Run the monitor with Docker commands
echo "Starting server monitoring in Docker with optimized real-time updates..."
bash run-monitor.sh
