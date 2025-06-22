#!/bin/bash

# This script runs the offline server downtime update command every 5 seconds
# to ensure real-time downtime metrics updates

while true; do
  echo "Running update-offline-downtime command..."
  docker-compose exec -T php php artisan server:update-offline-downtime
  echo "Sleeping for 5 seconds..."
  sleep 5
done
