#!/usr/bin/env bash
echo "Running deployment tasks..."

# Cache configurations and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed database (Optional, uncomment if you want to seed in production)
# php artisan db:seed --force

echo "Deployment tasks completed."
