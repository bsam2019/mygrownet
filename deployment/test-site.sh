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

echo "🧪 Testing site..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

echo "=== Checking maintenance mode status ==="
if [ -f storage/framework/down ]; then
    echo "⚠️  Site is in MAINTENANCE MODE"
    cat storage/framework/down
else
    echo "✅ Site is LIVE"
fi

echo ""
echo "=== Recent errors (last 20 lines) ==="
tail -20 storage/logs/laravel.log | grep "production.ERROR" || echo "No recent errors found!"

echo ""
echo "=== Checking PHP-FPM status ==="
sudo systemctl status php8.3-fpm --no-pager | head -10

echo ""
echo "=== Checking Nginx status ==="
sudo systemctl status nginx --no-pager | head -10

ENDSSH

echo ""
echo "🌐 Testing site accessibility..."
curl -I https://mygrownet.com 2>/dev/null | head -5

echo ""
echo "🎉 Test complete!"
