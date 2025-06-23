#!/bin/bash

echo "🔧 Running fixes for real-time chart updates"

# Build assets with latest JavaScript changes
echo "📦 Building frontend assets..."
npm run build

# Clear application cache
echo "🧹 Clearing application cache..."
docker-compose exec php php artisan optimize:clear

# Restart queue workers for improved real-time processing
echo "🔁 Restarting queue workers..."
docker-compose exec php php artisan queue:restart

# Run migrations (just in case)
echo "🗄️ Running migrations..."
docker-compose exec php php artisan migrate --force

echo "✅ All fixes applied! Now run your monitoring script: ./run-monitor.sh"
