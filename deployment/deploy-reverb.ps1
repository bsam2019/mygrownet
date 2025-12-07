# PowerShell script to deploy Reverb to production
# Run from Windows: .\deployment\deploy-reverb.ps1

$DROPLET_IP = "138.197.187.134"
$DROPLET_USER = "sammy"
$PROJECT_PATH = "/var/www/mygrownet.com"

Write-Host "🚀 Deploying Laravel Reverb to Production..." -ForegroundColor Green
Write-Host ""

# Create the deployment script on the server
$deployScript = @'
#!/bin/bash
set -e

cd /var/www/mygrownet.com

echo "📝 Updating .env file..."
# Check if Reverb config already exists
if ! grep -q "REVERB_APP_ID" .env; then
    cat >> .env << 'EOF'

# Laravel Reverb Configuration
REVERB_APP_ID=900327
REVERB_APP_KEY=kcxjrs9aggpmhrxgm1dr
REVERB_APP_SECRET=0fg9eluso8321saweww9
REVERB_HOST="mygrownet.com"
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

BROADCAST_CONNECTION=reverb
EOF
    echo "✅ Reverb configuration added to .env"
else
    echo "ℹ️  Reverb configuration already exists in .env"
fi

echo "📦 Installing Reverb package..."
composer require laravel/reverb --no-interaction

echo "⚙️  Creating systemd service..."
sudo tee /etc/systemd/system/reverb.service > /dev/null << 'EOF'
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

[Install]
WantedBy=multi-user.target
EOF

echo "🔄 Enabling and starting Reverb service..."
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl restart reverb

echo "🌐 Configuring Nginx..."
# Check if WebSocket config already exists
if ! sudo grep -q "location /app/" /etc/nginx/sites-available/mygrownet.com; then
    sudo tee -a /etc/nginx/sites-available/mygrownet.com > /dev/null << 'EOF'

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
    echo "✅ Nginx WebSocket configuration added"
else
    echo "ℹ️  Nginx WebSocket configuration already exists"
fi

echo "✅ Testing Nginx configuration..."
sudo nginx -t && sudo systemctl reload nginx

echo "🔥 Configuring firewall..."
sudo ufw allow 8080/tcp

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear

echo "🎨 Rebuilding frontend assets..."
npm run build

echo ""
echo "✅ Reverb deployment complete!"
echo ""
echo "📊 Service Status:"
sudo systemctl status reverb --no-pager -l
echo ""
echo "🔍 Recent logs:"
sudo journalctl -u reverb -n 20 --no-pager
'@

Write-Host "📤 Uploading deployment script..." -ForegroundColor Cyan
$deployScript | ssh "${DROPLET_USER}@${DROPLET_IP}" "cat > /tmp/deploy-reverb.sh && chmod +x /tmp/deploy-reverb.sh"

Write-Host "🚀 Executing deployment on server..." -ForegroundColor Cyan
Write-Host ""

ssh -t "${DROPLET_USER}@${DROPLET_IP}" "bash /tmp/deploy-reverb.sh"

Write-Host ""
Write-Host "✅ Deployment Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Yellow
Write-Host "  1. Test WebSocket connection at https://mygrownet.com"
Write-Host "  2. Monitor logs: ssh ${DROPLET_USER}@${DROPLET_IP} 'sudo journalctl -u reverb -f'"
Write-Host "  3. Check status: ssh ${DROPLET_USER}@${DROPLET_IP} 'sudo systemctl status reverb'"
Write-Host ""
