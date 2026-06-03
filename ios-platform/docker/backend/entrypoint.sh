#!/bin/sh
set -e

# Wait for DB
echo "Waiting for database..."
until nc -z "${DB_HOST:-mysql}" "${DB_PORT:-3306}"; do
    sleep 2
done

# Setup .env if missing
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate key + JWT secret if missing
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

if ! grep -q "^JWT_SECRET=" .env || [ -z "$(grep '^JWT_SECRET=' .env | cut -d= -f2)" ]; then
    echo "Generating JWT_SECRET..."
    SECRET=$(openssl rand -base64 32 | tr -d '=' | head -c 32)
    sed -i "s|^JWT_SECRET=.*|JWT_SECRET=${SECRET}|" .env
    php artisan jwt:secret --force
fi

# Storage symlink
php artisan storage:link 2>/dev/null || true

# Cache clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations (in production, you'd do this manually)
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

# Cache config in production
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "Laravel ready!"
exec "$@"
