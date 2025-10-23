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

echo "🔄 Regenerating Ziggy routes on production..."
echo "📍 Server: $DROPLET_IP"

# SSH and regenerate routes
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

echo "🧹 Clearing route cache..."
php artisan route:clear

echo "🔄 Regenerating Ziggy routes..."
php artisan ziggy:generate

echo "🚀 Optimizing..."
php artisan optimize

echo "✅ Routes regenerated!"

ENDSSH

echo "🎉 Done!"
