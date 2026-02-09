#!/usr/bin/env bash
echo "Running deployment tasks..."

# Cache configurations and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed database if RUN_SEEDER is true (case-insensitive)
if [ "$(echo "$RUN_SEEDER" | tr '[:upper:]' '[:lower:]')" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

# Create storage link
php artisan storage:link --force

echo "Deployment tasks completed."
