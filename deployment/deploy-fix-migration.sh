#!/bin/bash

# Load credentials
source .deploy-credentials

echo "🔧 Fixing migration issue on production..."
echo "📍 Server: $DROPLET_IP"

# SSH and run the fix command
ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

echo "🔧 Running fix command..."
php artisan fix:payment-migration

echo "🔄 Running remaining migrations..."
php artisan migrate --force

echo "✅ Fix complete!"
ENDSSH

echo "🎉 All done!"
