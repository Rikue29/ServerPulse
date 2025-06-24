#!/bin/bash
docker exec serverpulse-node-1 npm install
docker exec serverpulse-node-1 npm run build
docker exec php php artisan view:clear
docker exec php php artisan cache:clear
docker exec php php artisan config:clear
echo "Frontend assets rebuilt successfully!"
