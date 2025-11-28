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

echo "🐛 Enabling debug mode in production..."
echo "📍 Server: $DROPLET_IP"
echo "⚠️  WARNING: This will enable debug mode in production!"
echo "   Make sure to disable it after debugging!"

# Confirm action
read -p "Are you sure you want to enable debug mode? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Cancelled."
    exit 1
fi

# SSH and enable debug mode
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

echo "📋 Current environment settings:"
grep -E "APP_ENV|APP_DEBUG|LOG_LEVEL" .env

echo ""
echo "💾 Creating backup of current .env..."
cp .env .env.backup.\$(date +%Y%m%d_%H%M%S)

echo "🐛 Enabling debug mode..."
# Enable debug mode
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/g' .env
sed -i 's/APP_ENV=production/APP_ENV=local/g' .env
sed -i 's/LOG_LEVEL=error/LOG_LEVEL=debug/g' .env

# Add debug settings if they don't exist
if ! grep -q "APP_DEBUG" .env; then
    echo "APP_DEBUG=true" >> .env
fi

if ! grep -q "LOG_LEVEL" .env; then
    echo "LOG_LEVEL=debug" >> .env
fi

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "📋 New environment settings:"
grep -E "APP_ENV|APP_DEBUG|LOG_LEVEL" .env

echo ""
echo "✅ Debug mode enabled!"
echo "🔍 You can now see detailed error messages."
echo "📝 Check logs with: tail -f storage/logs/laravel.log"
echo ""
echo "⚠️  IMPORTANT: Remember to disable debug mode when done:"
echo "   Run: ./deployment/disable-debug.sh"

ENDSSH

echo "🎉 Debug mode enabled on production server!"
echo ""
echo "📝 To check logs from your local machine:"
echo "   ssh ${DROPLET_USER}@${DROPLET_IP} 'cd ${PROJECT_PATH} && tail -f storage/logs/laravel.log'"
echo ""
echo "🔍 To test the investor login issue:"
echo "   1. Try logging in as an investor"
echo "   2. Check the detailed error messages"
echo "   3. Monitor the logs for session-related issues"
echo ""
echo "⚠️  Don't forget to disable debug mode when done!"
