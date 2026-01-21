#!/bin/bash

# Update nginx to use *.www.mygrownet.com SSL certificate

# Load credentials
source .deploy-credentials

echo "🔧 Update WWW Redirect with SSL Certificate"
echo "==========================================="
echo ""

ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << 'ENDSSH'

echo "📁 Finding *.www.mygrownet.com certificate..."
CERT_DIR=$(sudo ls -d /etc/letsencrypt/live/*www.mygrownet.com* 2>/dev/null | head -1)

if [ -z "$CERT_DIR" ]; then
    echo "❌ Certificate not found!"
    echo "Please run: bash deployment/generate-www-wildcard-ssl.sh first"
    exit 1
fi

echo "✅ Found certificate at: $CERT_DIR"
echo ""

echo "📝 Updating nginx configuration..."
sudo tee /etc/nginx/sites-available/www-redirect > /dev/null << EOF
# WWW to Non-WWW Redirect for GrowBuilder Subdomains
# Redirects www.subdomain.mygrownet.com to subdomain.mygrownet.com

server {
    listen 80;
    listen [::]:80;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com\$;
    
    # Redirect to non-www
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com\$;
    
    # SSL certificate for *.www.mygrownet.com
    ssl_certificate $CERT_DIR/fullchain.pem;
    ssl_certificate_key $CERT_DIR/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
    # Redirect to non-www
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}
EOF

echo "✅ Config updated with certificate: $CERT_DIR"
echo ""

echo "🧪 Testing nginx configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo ""
    echo "🔄 Reloading nginx..."
    sudo systemctl reload nginx
    echo "✅ Nginx reloaded!"
else
    echo "❌ Nginx configuration has errors!"
    exit 1
fi

ENDSSH

echo ""
echo "✅ Complete!"
echo ""
echo "🧪 Test in browser:"
echo "https://www.chisambofarms.mygrownet.com"
echo ""
echo "Should redirect to:"
echo "https://chisambofarms.mygrownet.com"
