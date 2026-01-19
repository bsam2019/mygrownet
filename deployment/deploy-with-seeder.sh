#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Load credentials from .deploy-credentials file
if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    echo "Please create .deploy-credentials file in project root."
    exit 1
fi

echo "🚀 Running MarketplaceCategorySeeder on MyGrowNet droplet..."
echo "📍 Server: $DROPLET_IP"

# SSH and run seeder
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

echo "🌱 Running MarketplaceCategorySeeder..."
php artisan db:seed --class=MarketplaceCategorySeeder --force

echo "✅ Seeder completed!"

ENDSSH

echo "🎉 All done!"
