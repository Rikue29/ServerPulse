# ServerPulse Setup Guide for Windows

This guide will walk you through setting up ServerPulse on Windows from scratch.

## Prerequisites

### 1. Install Required Software

#### Docker Desktop for Windows
1. Download Docker Desktop from [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Run the installer and follow the setup wizard
3. Ensure WSL 2 is enabled during installation
4. Restart your computer after installation
5. Start Docker Desktop and wait for it to fully load

#### Git for Windows
1. Download Git from [https://git-scm.com/download/win](https://git-scm.com/download/win)
2. Run the installer with default settings
3. Verify installation by opening Command Prompt and running: `git --version`

#### Visual Studio Code (Recommended)
1. Download VS Code from [https://code.visualstudio.com/](https://code.visualstudio.com/)
2. Install with default settings
3. Install recommended extensions for PHP and Laravel

## Project Setup

### 1. Clone the Repository
```bash
# Open Command Prompt or PowerShell
cd C:\
git clone <your-repository-url> ServerPulse
cd ServerPulse
```

### 2. Environment Configuration

#### Create Environment File
```bash
# Copy the example environment file
copy .env.example .env
```

#### Configure Environment Variables
Edit `.env` file with your settings:
```env
APP_NAME=ServerPulse
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=serverpulse
DB_USERNAME=root
DB_PASSWORD=password

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=ap1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 3. Docker Setup

#### Start Docker Services
```bash
# Start the Docker containers
docker-compose up -d

# Check if containers are running
docker-compose ps
```

#### Install PHP Dependencies
```bash
# Install Composer dependencies
docker-compose exec php composer install

# Install Node.js dependencies
docker-compose exec php npm install
```

### 4. Database Setup

#### Run Migrations
```bash
# Generate application key
docker-compose exec php php artisan key:generate

# Run database migrations
docker-compose exec php php artisan migrate

# Seed the database (optional)
docker-compose exec php php artisan db:seed
```

#### Clear Caches
```bash
# Clear all caches to ensure fresh start
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan route:clear
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan view:clear
docker-compose exec php composer dump-autoload
docker-compose exec php php artisan optimize
```

### 5. Build Frontend Assets
```bash
# Build development assets
docker-compose exec php npm run dev

# Or build for production
docker-compose exec php npm run build
```

## Monitoring Setup

### 1. Start Monitoring Service

#### Option A: Quick Start (Manual)
```bash
# Run the monitoring script
run-monitor.bat
```

#### Option B: Background Mode
```bash
# Start monitoring in background
start-monitor.vbs
```

#### Option C: Auto-Start on Boot (Recommended)
```bash
# Run as Administrator
setup-autostart.bat
```

### 2. Verify Monitoring
1. Open your browser and go to `http://localhost:8000`
2. Navigate to the Servers page
3. Check that real-time updates are working
4. Verify that downtime tracking is functioning

## Troubleshooting

### Common Issues

#### Docker Not Starting
- Ensure Docker Desktop is running
- Check Windows Hyper-V is enabled
- Verify WSL 2 is properly configured

#### Permission Issues
```bash
# Run Command Prompt as Administrator
# Navigate to project directory
cd C:\ServerPulse
# Set proper permissions
icacls . /grant Everyone:F /T
```

#### Port Conflicts
If port 8000 is in use:
```bash
# Check what's using the port
netstat -ano | findstr :8000

# Kill the process or change port in docker-compose.yml
```

#### Database Connection Issues
```bash
# Restart MySQL container
docker-compose restart mysql

# Check MySQL logs
docker-compose logs mysql
```

### Reset Everything
```bash
# Stop all containers
docker-compose down

# Remove all containers and volumes
docker-compose down -v

# Rebuild containers
docker-compose up -d --build

# Reinstall dependencies
docker-compose exec php composer install
docker-compose exec php npm install

# Reset database
docker-compose exec php php artisan migrate:fresh --seed
```

## Development Workflow

### 1. Starting Development
```bash
# Start containers
docker-compose up -d

# Start monitoring (in separate terminal)
run-monitor.bat

# Start asset compilation (in separate terminal)
docker-compose exec php npm run dev
```

### 2. Making Changes
1. Edit files in your preferred editor
2. Changes are automatically reflected in the browser
3. For PHP changes, no restart needed
4. For frontend changes, assets are automatically compiled

### 3. Stopping Development
```bash
# Stop monitoring (Ctrl+C in monitoring terminal)
# Stop containers
docker-compose down
```

## Production Deployment

### 1. Environment Setup
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Generate production key
docker-compose exec php php artisan key:generate --force
```

### 2. Build Production Assets
```bash
# Build optimized assets
docker-compose exec php npm run build
```

### 3. Optimize Application
```bash
# Cache configuration
docker-compose exec php php artisan config:cache
docker-compose exec php php artisan route:cache
docker-compose exec php php artisan view:cache
```

## Maintenance

### Regular Tasks
```bash
# Update dependencies
docker-compose exec php composer update
docker-compose exec php npm update

# Clear old logs
docker-compose exec php php artisan log:clear

# Backup database
docker-compose exec mysql mysqldump -u root -p serverpulse > backup.sql
```

### Monitoring Health Check
```bash
# Check application status
docker-compose exec php php artisan route:list

# Test downtime tracking
docker-compose exec php php artisan test:downtime-tracking

# Check server metrics
docker-compose exec php php artisan servers:update-metrics
```

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review Docker logs: `docker-compose logs`
3. Check Laravel logs: `docker-compose exec php tail -f storage/logs/laravel.log`
4. Verify all prerequisites are installed correctly

## Quick Commands Reference

```bash
# Start everything
docker-compose up -d && run-monitor.bat

# Stop everything
docker-compose down

# View logs
docker-compose logs -f

# Access container shell
docker-compose exec php bash

# Run artisan commands
docker-compose exec php php artisan [command]

# Install new packages
docker-compose exec php composer require [package]
docker-compose exec php npm install [package]
```

---

**Note:** This guide assumes you're using Docker Desktop for Windows. If you're using a different setup (like WSL2 directly), some commands may vary. 