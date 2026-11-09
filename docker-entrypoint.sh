#!/bin/bash
set -e

# Run migrations if database is available
if [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true

    # Seed initial data if users table is empty
    USER_EXISTS=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null || echo "0")
    if [ "$USER_EXISTS" = "0" ]; then
        echo "Database is empty, seeding demo data and admin..."
        php artisan db:seed --force || true
    fi
fi

# Link storage
php artisan storage:link || true

# Execute original CMD
exec "$@"
