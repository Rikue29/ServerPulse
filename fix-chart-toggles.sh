#!/bin/bash

# Script to fix the chart toggle functionality in ServerPulse

echo "▶️ Building assets to apply chart toggle fixes..."
npm run build

echo "▶️ Clearing application cache..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear

echo "▶️ Restarting queue worker..."
php artisan queue:restart

echo "✅ Chart toggle fixes applied successfully."
echo "🔄 Please refresh your browser to see changes."
