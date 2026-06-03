#!/bin/bash
# ===========================================================
# Reset database (drop + re-migrate + seed)
# ===========================================================
set -e

cd "$(cd "$(dirname "${BASH_SOURCE[0]}")/../backend" && pwd)"

echo "⚠️  This will drop ALL data in the database!"
read -p "Are you sure? (y/N): " confirm

if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

php artisan migrate:fresh --seed --force
echo "✅ Database reset complete."
