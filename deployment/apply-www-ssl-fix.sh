#!/bin/bash

# Apply WWW SSL Fix - Update nginx to use *.www.mygrownet.com certificate
# This script should be run ON THE SERVER

echo "🔧 Applying WWW SSL Fix"
echo "======================="
echo ""

# Check if running with sudo
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run with sudo: sudo bash $0"
    exit 1
fi

echo "📁 Finding *.www.mygrownet.com certificate..."
CERT_DIR=$(ls -d /etc/letsencrypt/live/*www.mygrownet.com* 2>/dev/null | head -1)

if [ -z "$CERT_DIR" ]; then
    echo "❌ Certificate not found!"
    echo ""
    echo "Available certificates:"
    ls -la /etc/letsencrypt/live/
    exit 1
fi

echo "✅ Found certificate at: $CERT_DIR"
echo ""

echo "📝 Updating nginx configuration..."
cat > /etc/nginx/sites-available/www-redirect << EOF
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

# Enable the site if not already enabled
if [ ! -L /etc/nginx/sites-enabled/www-redirect ]; then
    echo "🔗 Enabling site..."
    ln -s /etc/nginx/sites-available/www-redirect /etc/nginx/sites-enabled/www-redirect
fi

echo "🧪 Testing nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo ""
    echo "🔄 Reloading nginx..."
    systemctl reload nginx
    echo "✅ Nginx reloaded!"
    echo ""
    echo "✅ Complete!"
    echo ""
    echo "🧪 Test in browser:"
    echo "   https://www.chisambofarms.mygrownet.com"
    echo ""
    echo "   Should redirect to:"
    echo "   https://chisambofarms.mygrownet.com"
else
    echo ""
    echo "❌ Nginx configuration has errors!"
    echo "Please check the configuration and try again."
    exit 1
fi
