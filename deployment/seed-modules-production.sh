#!/bin/bash

# Load credentials
source .deploy-credentials

echo "📦 Seeding modules in production..."
echo "📍 Server: $DROPLET_IP"

# SSH and seed modules
ssh -t $DROPLET_USER@$DROPLET_IP << 'ENDSSH'
cd /var/www/mygrownet.com

echo "📦 Running module seeder..."
sudo php artisan db:seed --class=ModuleSeeder --force

echo "🧹 Clearing cache..."
sudo php artisan cache:clear

echo "✅ Modules seeded successfully!"
ENDSSH

echo "🎉 Done! Home-hub should now show all modules."
