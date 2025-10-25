#!/bin/bash

# Load credentials
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    exit 1
fi

echo "🔧 Fixing shop tables on droplet..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

echo "📦 Creating products table..."
php artisan migrate --path=database/migrations/2025_10_24_174508_create_products_table.php --force

echo "🌱 Seeding shop data..."
php artisan db:seed --class=ShopSeeder --force

echo "✅ Shop tables fixed!"

ENDSSH

echo "🎉 Done!"
