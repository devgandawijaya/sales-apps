#!/usr/bin/env bash
set -e

# Wait for vendor/autoload.php if command is artisan worker or scheduler
if [[ "$*" == *"artisan"* ]] && [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Menunggu vendor/autoload.php sebelum menjalankan '$*'..."
    while [ ! -f "/var/www/html/vendor/autoload.php" ]; do
        sleep 2
    done
fi

# Set directory permissions for storage and bootstrap cache
if [ -d "/var/www/html/storage" ]; then
    chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
fi

exec "$@"
