# ServerPulse Setup Guide for macOS

This guide will walk you through setting up ServerPulse on macOS from scratch.

## Prerequisites

### 1. Install Required Software

#### Docker Desktop for Mac
1. Download Docker Desktop from [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Drag Docker.app to Applications folder
3. Open Docker Desktop from Applications
4. Wait for Docker to fully start (whale icon in menu bar should stop animating)
5. Accept the terms and conditions when prompted

#### Homebrew (Package Manager)
```bash
# Install Homebrew if not already installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Add Homebrew to PATH (if needed)
echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
eval "$(/opt/homebrew/bin/brew shellenv)"
```

#### Git
```bash
# Install Git via Homebrew
brew install git

# Verify installation
git --version
```

#### Visual Studio Code (Recommended)
```bash
# Install VS Code via Homebrew
brew install --cask visual-studio-code

# Or download from https://code.visualstudio.com/
```

## Project Setup

### 1. Clone the Repository
```bash
# Open Terminal
cd ~/Documents
git clone <your-repository-url> ServerPulse
cd ServerPulse
```

### 2. Environment Configuration

#### Create Environment File
```bash
# Copy the example environment file
cp .env.example .env
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
# Make the script executable
chmod +x run-monitor.sh

# Run the monitoring script
./run-monitor.sh
```

#### Option B: Background Mode
```bash
# Start monitoring in background
nohup ./run-monitor.sh > monitoring.log 2>&1 &

# Check if it's running
ps aux | grep run-monitor
```

#### Option C: Auto-Start on Boot (Recommended)
```bash
# Make the setup script executable
chmod +x setup-linux-cron.sh

# Run the setup script
./setup-linux-cron.sh
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
- Check Docker Desktop settings in menu bar
- Restart Docker Desktop if needed
- Verify Docker has necessary permissions

#### Permission Issues
```bash
# Fix file permissions
sudo chown -R $(whoami):$(whoami) .
chmod -R 755 storage bootstrap/cache

# Fix Docker volume permissions
docker-compose down
docker-compose up -d
```

#### Port Conflicts
If port 8000 is in use:
```bash
# Check what's using the port
lsof -i :8000

# Kill the process or change port in docker-compose.yml
```

#### Database Connection Issues
```bash
# Restart MySQL container
docker-compose restart mysql

# Check MySQL logs
docker-compose logs mysql
```

#### File System Performance Issues
```bash
# Add to Docker Desktop settings:
# Resources > File Sharing > Add your project directory
# Resources > Advanced > Increase memory limit to 4GB+
```

### Reset Everything
```bash
# Stop all containers
docker-compose down

# Remove all containers and volumes
docker-compose down -v

# Clean up Docker system
docker system prune -a

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
./run-monitor.sh

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

## macOS-Specific Optimizations

### 1. Docker Desktop Settings
- **Resources > Memory**: Set to 4GB or higher
- **Resources > CPUs**: Set to 2 or higher
- **Resources > Disk**: Increase disk image size
- **Resources > File Sharing**: Add your project directory
- **Docker Engine**: Enable BuildKit for faster builds

### 2. Terminal Setup
```bash
# Install Oh My Zsh for better terminal experience
sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

# Install useful tools
brew install tree
brew install htop
brew install jq
```

### 3. File System Performance
```bash
# Use delegated mode for better performance
# Add to docker-compose.yml volumes:
volumes:
  - .:/var/www/html:delegated
```

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review Docker logs: `docker-compose logs`
3. Check Laravel logs: `docker-compose exec php tail -f storage/logs/laravel.log`
4. Verify all prerequisites are installed correctly
5. Check Docker Desktop settings and resources

## Quick Commands Reference

```bash
# Start everything
docker-compose up -d && ./run-monitor.sh

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

# Quick restart
docker-compose restart

# Check container status
docker-compose ps

# View resource usage
docker stats
```

## Performance Tips for macOS

### 1. Docker Desktop Optimization
- Increase memory allocation to 4GB+
- Enable BuildKit for faster builds
- Use delegated volume mounting
- Exclude unnecessary directories from file sharing

### 2. Development Workflow
- Use VS Code with Docker extension
- Enable file watching for faster asset compilation
- Use multiple terminal tabs for different tasks
- Consider using iTerm2 for better terminal experience

### 3. System Resources
- Close unnecessary applications when developing
- Monitor Docker resource usage
- Restart Docker Desktop if performance degrades
- Keep macOS updated for best compatibility

---

**Note:** This guide is optimized for macOS with Apple Silicon (M1/M2) and Intel processors. Some commands may vary slightly between different macOS versions. 