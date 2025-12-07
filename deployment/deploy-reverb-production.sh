#!/bin/bash

# Laravel Reverb Production Configuration Script
# Run this from your LOCAL machine to configure Reverb on production server
# NOTE: Reverb package is already installed, this script just configures it
# Usage: bash deployment/deploy-reverb-production.sh

set -e

echo "=========================================="
echo "Laravel Reverb Production Configuration"
echo "=========================================="

# Load credentials
if [ ! -f .deploy-credentials ]; then
    echo "Error: .deploy-credentials file not found!"
    exit 1
fi

source .deploy-credentials

echo ""
echo "Target Server: $DROPLET_IP"
echo "User: $DROPLET_USER"
echo "Project Path: $PROJECT_PATH"
echo ""
read -p "Continue with deployment? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled."
    exit 0
fi

# Create remote setup script
cat > /tmp/reverb-setup-remote.sh << 'REMOTE_SCRIPT'
#!/bin/bash
set -e

PROJECT_PATH="/var/www/mygrownet.com"
cd $PROJECT_PATH

echo "=========================================="
echo "Step 1: Updating .env configuration"
echo "=========================================="

# Backup .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Remove old Reverb config if exists
sed -i '/# Laravel Reverb Configuration/,/BROADCAST_CONNECTION=reverb/d' .env

# Add new Reverb configuration
cat >> .env << 'EOF'

# Laravel Reverb Configuration
REVERB_APP_ID=900327
REVERB_APP_KEY=kcxjrs9aggpmhrxgm1dr
REVERB_APP_SECRET=0fg9eluso8321saweww9
REVERB_HOST="mygrownet.com"
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

BROADCAST_CONNECTION=reverb
EOF

echo "✓ .env updated"

echo ""
echo "=========================================="
echo "Step 2: Creating systemd service"
echo "=========================================="

sudo tee /etc/systemd/system/reverb.service > /dev/null << 'SERVICE'
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/mygrownet.com
ExecStart=/usr/bin/php /var/www/mygrownet.com/artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=3
StandardOutput=append:/var/log/reverb.log
StandardError=append:/var/log/reverb.log

[Install]
WantedBy=multi-user.target
SERVICE

echo "✓ Systemd service created"

echo ""
echo "=========================================="
echo "Step 3: Configuring Nginx"
echo "=========================================="

# Backup Nginx config
sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup.$(date +%Y%m%d_%H%M%S)

# Check if WebSocket config already exists
if grep -q "Laravel Reverb WebSocket" /etc/nginx/sites-available/mygrownet.com; then
    echo "✓ Nginx WebSocket configuration already exists"
else
    # Add WebSocket location block
    sudo sed -i '/location \/ {/i \    # Laravel Reverb WebSocket Configuration\n    location /app/ {\n        proxy_pass http://127.0.0.1:8080;\n        proxy_http_version 1.1;\n        proxy_set_header Upgrade $http_upgrade;\n        proxy_set_header Connection "upgrade";\n        proxy_set_header Host $host;\n        proxy_set_header X-Real-IP $remote_addr;\n        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\n        proxy_set_header X-Forwarded-Proto $scheme;\n        proxy_read_timeout 86400;\n    }\n' /etc/nginx/sites-available/mygrownet.com
    
    echo "✓ Nginx WebSocket configuration added"
fi

# Test Nginx configuration
sudo nginx -t
echo "✓ Nginx configuration valid"

echo ""
echo "=========================================="
echo "Step 4: Configuring firewall"
echo "=========================================="

sudo ufw allow 8080/tcp
echo "✓ Firewall configured"

echo ""
echo "=========================================="
echo "Step 5: Setting permissions"
echo "=========================================="

sudo chown -R www-data:www-data $PROJECT_PATH
sudo chmod -R 755 $PROJECT_PATH
sudo chmod -R 775 $PROJECT_PATH/storage
sudo chmod -R 775 $PROJECT_PATH/bootstrap/cache
echo "✓ Permissions set"

echo ""
echo "=========================================="
echo "Step 6: Clearing caches"
echo "=========================================="

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"

echo ""
echo "=========================================="
echo "Step 7: Rebuilding frontend assets"
echo "=========================================="

npm run build
echo "✓ Assets built"

echo ""
echo "=========================================="
echo "Step 8: Starting services"
echo "=========================================="

sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl restart reverb
sudo systemctl reload nginx
echo "✓ Services started"

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Service Status:"
sudo systemctl status reverb --no-pager -l
echo ""
echo "WebSocket Endpoint: wss://mygrownet.com/app"
echo ""
echo "To view logs: sudo journalctl -u reverb -f"
echo "To restart: sudo systemctl restart reverb"
echo ""

REMOTE_SCRIPT

echo ""
echo "Uploading setup script to server..."
scp -o StrictHostKeyChecking=no /tmp/reverb-setup-remote.sh $DROPLET_USER@$DROPLET_IP:/tmp/

echo ""
echo "Connecting to server and running setup..."
echo "(You may be prompted for sudo password: $DROPLET_SUDO_PASSWORD)"
echo ""

ssh -o StrictHostKeyChecking=no -t $DROPLET_USER@$DROPLET_IP "bash /tmp/reverb-setup-remote.sh"

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Reverb is now running on production!"
echo "WebSocket endpoint: wss://mygrownet.com/app"
echo ""
echo "Next steps:"
echo "1. Test WebSocket connection from your app"
echo "2. Monitor logs: ssh $DROPLET_USER@$DROPLET_IP 'sudo journalctl -u reverb -f'"
echo "3. Check status: ssh $DROPLET_USER@$DROPLET_IP 'sudo systemctl status reverb'"
echo ""

# Cleanup
rm /tmp/reverb-setup-remote.sh

