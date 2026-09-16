#!/bin/bash
set -e

# Replace Nginx port if $PORT environment variable is set (used by Railway/Render)
if [ -n "$PORT" ]; then
    sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-available/default
fi

# Run Laravel setup commands
echo "Running migrations..."
php artisan migrate --force

echo "Clearing and caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Supervisord..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
