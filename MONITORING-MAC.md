# ServerPulse Monitoring on macOS

This guide explains how to run the ServerPulse monitoring system on macOS using Docker.

## Prerequisites

1. Docker Desktop for Mac installed and running
2. Git (for cloning the repository if needed)
3. Basic terminal knowledge

## Setup Instructions

### 1. Environment Setup

First, create your environment file:

```bash
cp .env.example .env
```

### 2. Start the Docker Containers

Make sure all your Docker containers are running:

```bash
cd /path/to/ServerPulse
docker-compose up -d
```

### 3. Install Dependencies and Set Up Database

Run the following commands to install PHP dependencies and set up the application:

```bash
docker-compose exec php bash -c "composer install --no-interaction && \
    php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache"
```

### 4. Set Permissions

Ensure proper permissions are set:

```bash
chmod -R 775 storage bootstrap/cache
```

### 5. Run the Monitoring System

Make the monitoring script executable (only needed once):

```bash
chmod +x run-monitor.sh
```

Start the monitoring system:

```bash
./run-monitor.sh
```

This will:
1. Start the queue worker in the background
2. Begin monitoring your servers
3. Update metrics every 15 seconds

### 6. Stopping the Monitoring

To stop the monitoring:
1. Press `Ctrl+C` in the terminal where the script is running
2. Stop the queue worker:
```bash
docker-compose exec php php artisan queue:clear
docker-compose exec php php artisan queue:flush
```

## Troubleshooting

### If you get permission errors:

```bash
chmod -R 775 storage bootstrap/cache
docker-compose exec php chown -R www-data:www-data storage bootstrap/cache
```

### If the queue worker isn't processing jobs:

```bash
docker-compose exec php php artisan queue:restart
```

### To view logs:

```bash
docker-compose logs -f
# or check Laravel logs
docker-compose exec php tail -f storage/logs/laravel.log
```

### To restart everything:

```bash
docker-compose down
docker-compose up -d
```

## Notes

- The monitoring system will automatically restart if it crashes
- Check the logs in `storage/logs/` for any issues
- Make sure your `.env` file is properly configured with your database and monitoring settings
- If using an Apple Silicon Mac (M1/M2), uncomment the `platform: linux/amd64` line in `docker-compose.yml` if you experience any issues
