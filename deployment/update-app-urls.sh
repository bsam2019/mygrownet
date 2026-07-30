#!/bin/bash

# Load credentials
source .deploy-credentials

echo "🔄 Updating application URLs on production..."
echo "📍 Server: $DROPLET_IP"
echo ""

# SSH and run seeder
sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no root@$DROPLET_IP << 'EOF'
cd /var/www/mygrownet.com

echo "📦 Running ApplicationRegistrySeeder..."
php artisan db:seed --class=ApplicationRegistrySeeder

echo ""
echo "✅ Application URLs updated successfully!"
echo ""
echo "🔍 Verifying URLs..."
php artisan tinker --execute="
\$apps = App\Domain\Core\Models\Application::whereIn('slug', ['grownet', 'zamstay', 'stockflow', 'bms'])->get(['slug', 'url']);
foreach (\$apps as \$app) {
    echo \$app->slug . ': ' . \$app->url . PHP_EOL;
}
"
EOF

echo ""
echo "✅ Done! Workspace should now redirect to correct subdomain URLs"
