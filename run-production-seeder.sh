#!/bin/bash

# Run Production Seeder on MyGrowNet Server

SERVER_HOST="138.197.187.134"
SERVER_USER="sammy"

echo "🌱 Running MyGrowNet Production Seeder..."

ssh "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
SUDO_PASS="Bsam@2025!!"

cd /var/www/mygrownet.com

echo "🔍 Checking current database state..."
echo "$SUDO_PASS" | sudo -S -u www-data php artisan db:show

echo ""
echo "🌱 Running production seeder..."
echo "$SUDO_PASS" | sudo -S -u www-data php artisan db:seed --class=ProductionSeeder --force

echo ""
echo "✅ Production seeding complete!"
ENDSSH
