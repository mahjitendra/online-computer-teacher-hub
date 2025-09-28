#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "Starting deployment..."

# 1. Install Composer dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# 2. Run database migrations
echo "Running database migrations..."
php scripts/migrate.php

# 3. Optional: Clear cache (if a caching system were implemented)
# echo "Clearing application cache..."
# php scripts/clear-cache.php

echo "Deployment finished successfully!"