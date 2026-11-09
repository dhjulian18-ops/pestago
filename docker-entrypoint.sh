#!/bin/bash
set -e

# Run migrations if database is available
if [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

# Link storage
php artisan storage:link || true

# Execute original CMD
exec "$@"
