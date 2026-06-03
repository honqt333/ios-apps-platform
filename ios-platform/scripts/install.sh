#!/bin/bash
# ===========================================================
# iOS Apps Platform — one-shot installer
# ===========================================================
set -e

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "🚀 iOS Apps Platform — Installation"
echo "==================================="

# Backend
echo ""
echo "📦 Installing backend dependencies..."
cd "$ROOT_DIR/backend"
[ -f .env ] || cp .env.example .env
composer install --no-interaction --prefer-dist

echo "🔑 Generating keys..."
php artisan key:generate --force
SECRET=$(openssl rand -base64 32 | tr -d '=' | head -c 32)
sed -i "s|^JWT_SECRET=.*|JWT_SECRET=${SECRET}|" .env
php artisan jwt:secret --force

# Frontend
echo ""
echo "📦 Installing frontend dependencies..."
cd "$ROOT_DIR/frontend"
[ -f .env ] || cp .env.example .env
npm install

echo ""
echo "✅ Installation complete!"
echo ""
echo "Next steps:"
echo "  1. Configure backend/.env (DB credentials, storage disk, etc.)"
echo "  2. Run migrations:    cd backend && php artisan migrate --seed"
echo "  3. Start backend:     cd backend && php artisan serve"
echo "  4. Start frontend:    cd frontend && npm run dev"
echo ""
echo "Or use Docker:        docker compose up -d --build"
echo ""
