#!/bin/bash

echo "🐳 ServerPulse Docker Cleanup Tool"
echo "=================================="
echo "This script will clean up unused Docker resources to reduce system load."

# Get initial stats
echo ""
echo "Current container status:"
docker ps -a

echo ""
echo "Current disk usage by Docker:"
docker system df

# Confirm with user
echo ""
read -p "Do you want to proceed with cleanup? (y/n): " confirm

if [[ $confirm != [yY] && $confirm != [yY][eE][sS] ]]; then
    echo "Cleanup cancelled."
    exit 0
fi

echo ""
echo "🧹 Cleaning up unused Docker resources..."

# Stop any unused containers
echo ""
echo "Stopping unused containers..."
for container in $(docker ps -q -f "status=exited"); do
    container_name=$(docker inspect --format='{{.Name}}' $container | sed 's/\///')
    echo "  Stopping $container_name..."
    docker stop $container > /dev/null
done

# Remove containers that haven't been used in the last 24 hours
echo ""
echo "Removing old stopped containers..."
docker container prune --filter "until=24h" -f

# Remove dangling images
echo ""
echo "Removing unused images..."
docker image prune -f

# Remove unused volumes
echo ""
echo "Removing unused volumes..."
docker volume prune -f

# Remove unused networks
echo ""
echo "Removing unused networks..."
docker network prune -f

# Get system info after cleanup
echo ""
echo "Container status after cleanup:"
docker ps -a

echo ""
echo "Docker disk usage after cleanup:"
docker system df

echo ""
echo "✅ Docker cleanup completed!"
echo ""
echo "💡 Tip: You may want to restart your Docker daemon to free up memory."
echo "    To do this, restart Docker Desktop or run 'sudo systemctl restart docker'"
echo "    on Linux systems."
