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

echo "🔒 Disabling debug mode in production..."
echo "📍 Server: $DROPLET_IP"

# SSH and disable debug mode
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

echo "📋 Current environment settings:"
grep -E "APP_ENV|APP_DEBUG|LOG_LEVEL" .env

echo ""
echo "🔒 Disabling debug mode..."
# Disable debug mode
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env
sed -i 's/APP_ENV=local/APP_ENV=production/g' .env
sed -i 's/LOG_LEVEL=debug/LOG_LEVEL=error/g' .env

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "🚀 Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "📋 New environment settings:"
grep -E "APP_ENV|APP_DEBUG|LOG_LEVEL" .env

echo ""
echo "✅ Debug mode disabled!"
echo "🔒 Production security restored."

ENDSSH

echo "🎉 Debug mode disabled on production server!"
echo "🔒 Your production environment is now secure again."