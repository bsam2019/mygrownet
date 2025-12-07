#!/bin/bash

# Laravel Reverb Production Setup Script (Remote Execution)
# This version is designed to be run remotely with password passed

set -e

SUDO_PASSWORD="$1"

echo "=========================================="
echo "Laravel Reverb Production Setup"
echo "=========================================="

PROJECT_PATH="/var/www/mygrownet.com"

# Check if running on production server
if [ ! -d "$PROJECT_PATH" ]; then
    echo "Error: Project path not found. Are you on the production server?"
    exit 1
fi

cd $PROJECT_PATH

echo ""
echo "Step 1: Adding Reverb configuration to .env..."
echo "----------------------------------------------"

# Check if Reverb config already exists
if grep -q "REVERB_APP_ID" .env; then
    echo "Reverb configuration already exists in .env"
else
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
    echo "Reverb configuration added to .env"
fi

echo ""
echo "Step 2: Creating systemd service..."
echo "------------------------------------"

echo "$SUDO_PASSWORD" | sudo -S tee /etc/systemd/system/reverb.service > /dev/null << 'EOF'
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
EOF

echo "Systemd service created"

echo ""
echo "Step 3: Creating Nginx WebSocket configuration..."
echo "--------------------------------------------------"

# Check if WebSocket config already exists in Nginx
if echo "$SUDO_PASSWORD" | sudo -S grep -q "Laravel Reverb WebSocket" /etc/nginx/sites-available/mygrownet.com 2>/dev/null; then
    echo "Nginx WebSocket configuration already exists"
else
    # Create a backup
    echo "$SUDO_PASSWORD" | sudo -S cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup
    
    # Add WebSocket location block before the last closing brace
    echo "$SUDO_PASSWORD" | sudo -S sed -i '/^}$/i \
    # Laravel Reverb WebSocket Configuration\
    location /app/ {\
        proxy_pass http://127.0.0.1:8080;\
        proxy_http_version 1.1;\
        proxy_set_header Upgrade $http_upgrade;\
        proxy_set_header Connection "upgrade";\
        proxy_set_header Host $host;\
        proxy_set_header X-Real-IP $remote_addr;\
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\
        proxy_set_header X-Forwarded-Proto $scheme;\
        proxy_read_timeout 86400;\
    }\
' /etc/nginx/sites-available/mygrownet.com
    
    echo "Nginx WebSocket configuration added"
fi

echo ""
echo "Step 4: Testing Nginx configuration..."
echo "---------------------------------------"
echo "$SUDO_PASSWORD" | sudo -S nginx -t

echo ""
echo "Step 5: Enabling and starting services..."
echo "------------------------------------------"
echo "$SUDO_PASSWORD" | sudo -S systemctl daemon-reload
echo "$SUDO_PASSWORD" | sudo -S systemctl enable reverb
echo "$SUDO_PASSWORD" | sudo -S systemctl restart reverb
echo "$SUDO_PASSWORD" | sudo -S systemctl reload nginx

echo ""
echo "Step 6: Configuring firewall..."
echo "--------------------------------"
echo "$SUDO_PASSWORD" | sudo -S ufw allow 8080/tcp || true

echo ""
echo "Step 7: Clearing caches..."
echo "--------------------------"
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo ""
echo "Step 8: Rebuilding frontend assets..."
echo "--------------------------------------"
npm run build

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Reverb Status:"
echo "$SUDO_PASSWORD" | sudo -S systemctl status reverb --no-pager
echo ""
echo "To view logs: sudo journalctl -u reverb -f"
echo "WebSocket endpoint: wss://mygrownet.com/app"
echo ""
