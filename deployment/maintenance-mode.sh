#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Load credentials
if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    exit 1
fi

echo "🔧 Enabling maintenance mode..."
echo "📍 Server: $DROPLET_IP"

# Enable maintenance mode
ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com
php artisan down --retry=60
echo "✅ Maintenance mode enabled"

# Check recent error logs
echo ""
echo "📋 Recent Laravel error logs (last 200 lines):"
tail -200 storage/logs/laravel.log | grep -A 20 "production.ERROR"

ENDSSH

echo "🎉 Done!"
