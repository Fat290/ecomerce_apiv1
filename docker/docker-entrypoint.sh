#!/bin/sh
set -e

echo "🚀 Starting Laravel Application..."

# Wait for database to be ready (if using external DB)
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" != "sqlite" ]; then
    echo "⏳ Waiting for database connection..."
    max_tries=30
    tries=0

    while [ $tries -lt $max_tries ]; do
        if [ "$DB_CONNECTION" = "mysql" ]; then
            if mysqladmin ping -h"$DB_HOST" -P"${DB_PORT:-3306}" --silent; then
                echo "✅ Database connection established"
                break
            fi
        elif [ "$DB_CONNECTION" = "pgsql" ]; then
            if pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" > /dev/null 2>&1; then
                echo "✅ Database connection established"
                break
            fi
        fi

        tries=$((tries + 1))
        echo "   Attempt $tries/$max_tries..."
        sleep 2
    done

    if [ $tries -eq $max_tries ]; then
        echo "⚠️  Warning: Could not connect to database after $max_tries attempts"
        echo "   Continuing anyway..."
    fi
fi

# Create storage directories if they don't exist
echo "📁 Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Cache configuration
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "🗄️  Running database migrations..."
    php artisan migrate --force --no-interaction || {
        echo "⚠️  Migration failed, but continuing..."
    }
fi

# Seed database (optional, only if env var is set)
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force --no-interaction || {
        echo "⚠️  Seeding failed, but continuing..."
    }
fi

# Link storage
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Linking storage..."
    php artisan storage:link || true
fi

# Generate JWT secret if not exists
if [ -z "$JWT_SECRET" ]; then
    echo "🔐 Generating JWT secret..."
    php artisan jwt:secret --force || true
fi

# Clear any existing caches
echo "🧹 Clearing old caches..."
php artisan cache:clear || true
php artisan queue:restart || true

echo "✅ Laravel application is ready!"
echo "🌐 Starting web server on port 8080..."

# Execute the main command
exec "$@"

