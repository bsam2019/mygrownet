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

echo "🔓 Disabling maintenance mode..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com
php artisan up
echo "✅ Site is now live"
ENDSSH

echo "🎉 Done!"
