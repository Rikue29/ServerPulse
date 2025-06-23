#!/bin/bash

# Define color codes for better readability
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=========================================${NC}"
echo -e "${YELLOW}ServerPulse Monitor Docker Launcher${NC}"
echo -e "${BLUE}=========================================${NC}"

# Navigate to the project directory
cd "$(dirname "$0")"

# Check if Docker is running
echo -e "${BLUE}[1/5]${NC} Checking Docker status..."
if ! docker info > /dev/null 2>&1; then
  echo -e "${RED}Error: Docker is not running. Please start Docker and try again.${NC}"
  exit 1
fi
echo -e "${GREEN}✓ Docker is running${NC}"

# Find the PHP container name
echo -e "${BLUE}[2/5]${NC} Finding PHP container..."
PHP_CONTAINER=$(docker ps | grep -i php | awk '{print $1}' | head -n1)

if [ -z "$PHP_CONTAINER" ]; then
  echo -e "${RED}Error: PHP container not found. Make sure your Docker environment is running properly.${NC}"
  exit 1
fi
echo -e "${GREEN}✓ PHP container found: $PHP_CONTAINER${NC}"

# Run database migrations to add any missing fields
echo -e "${BLUE}[3/5]${NC} Running database migrations..."
docker exec -it $PHP_CONTAINER php artisan migrate --force

# Build frontend assets (ensures JS changes are compiled)
echo -e "${BLUE}[4/5]${NC} Building frontend assets..."
docker exec -it $PHP_CONTAINER npm run build

# Run the monitoring script
echo -e "${BLUE}[5/5]${NC} Starting server monitoring..."
echo -e "${GREEN}Starting ServerPulse monitoring system...${NC}"
echo -e "${YELLOW}Press Ctrl+C to stop the monitoring process${NC}"
echo -e "${BLUE}=========================================${NC}"

# Run the monitor command directly in the PHP container
docker exec -it $PHP_CONTAINER php artisan monitor:server

echo -e "${BLUE}=========================================${NC}"
echo -e "${RED}Monitoring stopped${NC}"
