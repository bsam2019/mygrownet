#!/bin/bash

# Step-by-step Reverb setup for remote execution
# Usage: Run this locally, it will SSH and execute commands

DROPLET_IP="138.197.187.134"
DROPLET_USER="sammy"
PROJECT_PATH="/var/www/mygrownet.com"

echo "=========================================="
echo "Laravel Reverb Production Setup"
echo "=========================================="

echo ""
echo "Step 1: Checking .env configuration..."
ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "cd $PROJECT_PATH && grep -q 'REVERB_APP_ID' .env && echo '✓ Reverb config exists' || echo '✗ Config missing'"

echo ""
echo "Step 2: Creating systemd service file locally..."
cat > /tmp/reverb.service << 'EOF'
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

echo "✓ Service file created locally"

echo ""
echo "Step 3: Copying service file to server..."
scp -o StrictHostKeyChecking=no /tmp/reverb.service $DROPLET_USER@$DROPLET_IP:/tmp/reverb.service
echo "✓ Service file copied"

echo ""
echo "Step 4: Installing service file (requires sudo)..."
echo "Please enter sudo password when prompted..."
ssh -t -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "sudo mv /tmp/reverb.service /etc/systemd/system/reverb.service && sudo chmod 644 /etc/systemd/system/reverb.service"

echo ""
echo "Step 5: Creating Nginx WebSocket configuration..."
cat > /tmp/nginx-reverb.conf << 'EOF'
    # Laravel Reverb WebSocket Configuration
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
EOF

scp -o StrictHostKeyChecking=no /tmp/nginx-reverb.conf $DROPLET_USER@$DROPLET_IP:/tmp/nginx-reverb.conf
echo "✓ Nginx config created"

echo ""
echo "Step 6: Updating Nginx configuration (requires sudo)..."
ssh -t -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << 'SSHEOF'
# Check if config already exists
if sudo grep -q "Laravel Reverb WebSocket" /etc/nginx/sites-available/mygrownet.com 2>/dev/null; then
    echo "✓ Nginx WebSocket configuration already exists"
else
    # Backup
    sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup
    # Insert before last }
    sudo sed -i '/^}$/i \    # Laravel Reverb WebSocket Configuration\n    location /app/ {\n        proxy_pass http://127.0.0.1:8080;\n        proxy_http_version 1.1;\n        proxy_set_header Upgrade $http_upgrade;\n        proxy_set_header Connection "upgrade";\n        proxy_set_header Host $host;\n        proxy_set_header X-Real-IP $remote_addr;\n        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\n        proxy_set_header X-Forwarded-Proto $scheme;\n        proxy_read_timeout 86400;\n    }' /etc/nginx/sites-available/mygrownet.com
    echo "✓ Nginx configuration updated"
fi
SSHEOF

echo ""
echo "Step 7: Testing Nginx configuration..."
ssh -t -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "sudo nginx -t"

echo ""
echo "Step 8: Enabling and starting services..."
ssh -t -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << 'SSHEOF'
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl restart reverb
sudo systemctl reload nginx
sudo ufw allow 8080/tcp || true
echo "✓ Services started"
SSHEOF

echo ""
echo "Step 9: Clearing Laravel caches..."
ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "cd $PROJECT_PATH && php artisan config:clear && php artisan cache:clear && php artisan route:clear"
echo "✓ Caches cleared"

echo ""
echo "Step 10: Rebuilding frontend assets..."
ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "cd $PROJECT_PATH && npm run build"

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Checking Reverb status..."
ssh -t -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "sudo systemctl status reverb --no-pager"
echo ""
echo "WebSocket endpoint: wss://mygrownet.com/app"
echo "To view logs: ssh $DROPLET_USER@$DROPLET_IP 'sudo journalctl -u reverb -f'"
echo ""
