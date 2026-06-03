#!/bin/bash
# ===========================================================
# Generate JWT secret + APP_KEY for backend
# ===========================================================
set -e

cd "$(cd "$(dirname "${BASH_SOURCE[0]}")/../backend" && pwd)"

[ -f .env ] || cp .env.example .env

echo "🔑 Generating APP_KEY..."
php artisan key:generate --force

echo "🔑 Generating JWT_SECRET..."
SECRET=$(openssl rand -base64 32 | tr -d '=' | head -c 32)
sed -i "s|^JWT_SECRET=.*|JWT_SECRET=${SECRET}|" .env
php artisan jwt:secret --force

echo "✅ Secrets generated."
