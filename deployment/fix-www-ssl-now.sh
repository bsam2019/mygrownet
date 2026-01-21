#!/bin/bash

# Fix WWW SSL by using the correct wildcard certificate

# Load credentials
source .deploy-credentials

echo "🔧 Fixing WWW SSL Certificate"
echo "=============================="
echo ""

echo "The issue: nginx is using /etc/letsencrypt/live/mygrownet.com/"
echo "which only covers: mygrownet.com, www.mygrownet.com"
echo ""
echo "The fix: Switch to /etc/letsencrypt/live/mygrownet.com-0001/"
echo "which covers: mygrownet.com, *.mygrownet.com (wildcard)"
echo ""

ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH

cd /var/www/mygrownet.com

echo "📝 Updating www-redirect nginx config..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S tee /etc/nginx/sites-available/www-redirect > /dev/null << 'EOF'
# WWW to Non-WWW Redirect for GrowBuilder Subdomains
# Redirects www.subdomain.mygrownet.com to subdomain.mygrownet.com

server {
    listen 80;
    listen [::]:80;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com$;
    
    # Redirect to non-www
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com$;
    
    # SSL certificate (wildcard) - FIXED: Using mygrownet.com-0001 which has wildcard
    ssl_certificate /etc/letsencrypt/live/mygrownet.com-0001/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mygrownet.com-0001/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
    # Redirect to non-www
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}
EOF

echo "✅ Config updated"
echo ""

echo "🧪 Testing nginx configuration..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S nginx -t

echo ""
echo "🔄 Reloading nginx..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S systemctl reload nginx

echo ""
echo "✅ Nginx reloaded!"

ENDSSH

echo ""
echo "✅ Fix complete!"
echo ""
echo "🧪 Testing..."
curl -I https://www.chisambofarms.mygrownet.com 2>&1 | head -5

echo ""
echo "📝 Please test in browser:"
echo "https://www.chisambofarms.mygrownet.com"
echo ""
echo "Should redirect to:"
echo "https://chisambofarms.mygrownet.com"
