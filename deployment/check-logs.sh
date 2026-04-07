#!/bin/bash

# Load credentials
source .deploy-credentials

echo "📋 Checking production logs..."
echo "📍 Server: $DROPLET_IP"

# SSH and check logs
echo "$DROPLET_SUDO_PASSWORD" | ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << 'ENDSSH'
cd /var/www/mygrownet.com

echo "=== Last 50 lines of Laravel log ==="
tail -n 50 storage/logs/laravel.log

echo ""
echo "=== Checking for recent errors ==="
grep -i "error\|exception\|fatal" storage/logs/laravel.log | tail -n 20

ENDSSH

echo "✅ Log check complete!"
