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

echo "📋 Checking error logs on server..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

echo "=== Laravel Error Log (Last 100 lines) ==="
tail -100 storage/logs/laravel.log

echo ""
echo "=== PHP Error Log (Last 50 lines) ==="
sudo tail -50 /var/log/php*.log 2>/dev/null || echo "No PHP error log found"

echo ""
echo "=== Nginx Error Log (Last 50 lines) ==="
sudo tail -50 /var/log/nginx/error.log 2>/dev/null || echo "No Nginx error log found"

ENDSSH
