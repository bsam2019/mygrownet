#!/bin/bash

# Apply Dynamic Manifest Fix for GrowBuilder Sites

set -e

echo "=========================================="
echo "Applying Dynamic Manifest Fix"
echo "=========================================="

# Load credentials
if [ -f .deploy-credentials ]; then
    source .deploy-credentials
else
    echo "Error: .deploy-credentials file not found"
    exit 1
fi

echo "Connecting to server: $DROPLET_USER@$DROPLET_IP"

# Deploy via SSH
ssh $DROPLET_USER@$DROPLET_IP << ENDSSH
    set -e
    
    echo "Navigating to project directory..."
    cd /var/www/mygrownet.com
    
    echo "Pulling latest changes..."
    git pull origin main
    
    echo "Backing up static manifest.json..."
    if [ -f public/manifest.json ]; then
        mv public/manifest.json public/manifest.json.backup
        echo "Backed up to public/manifest.json.backup"
    fi
    
    echo "Updating Nginx configuration..."
    echo '$DROPLET_SUDO_PASSWORD' | sudo -S tee /etc/nginx/snippets/manifest-rewrite.conf > /dev/null << 'EOF'
# Dynamic manifest.json rewrite
location = /manifest.json {
    try_files \$uri /manifest.php;
}

location = /manifest.php {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
}
EOF
    
    echo "Checking if snippet is already included in Nginx config..."
    if ! grep -q "manifest-rewrite.conf" /etc/nginx/sites-available/mygrownet.com; then
        echo "Adding snippet to Nginx config..."
        echo '$DROPLET_SUDO_PASSWORD' | sudo -S sed -i '/location \/ {/i\    # Dynamic manifest\n    include snippets/manifest-rewrite.conf;\n' /etc/nginx/sites-available/mygrownet.com
    else
        echo "Snippet already included in Nginx config"
    fi
    
    echo "Testing Nginx configuration..."
    echo '$DROPLET_SUDO_PASSWORD' | sudo -S nginx -t
    
    echo "Reloading Nginx..."
    echo '$DROPLET_SUDO_PASSWORD' | sudo -S systemctl reload nginx
    
    echo "Clearing Laravel caches..."
    php artisan config:clear
    php artisan route:clear
    php artisan cache:clear
    
    echo "Deployment complete!"
ENDSSH

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Testing manifest:"
echo "  curl https://chisambofarms.mygrownet.com/manifest.json"
echo ""
echo "Expected: Should redirect to site-specific manifest"
echo ""
