#!/bin/bash

# Auto-fix WWW SSL Issue
# Automatically detects and fixes SSL certificate configuration

set -e

echo "🔧 Auto-fixing WWW SSL Issue..."
echo "================================"
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run with sudo"
    exit 1
fi

echo "1️⃣ Finding SSL certificate location..."
echo "-------------------------------------"

# Find the correct certificate directory
CERT_DIR=""
if [ -d "/etc/letsencrypt/live/mygrownet.com" ]; then
    CERT_DIR="/etc/letsencrypt/live/mygrownet.com"
    echo "✅ Found certificate at: $CERT_DIR"
elif [ -d "/etc/letsencrypt/live/www.mygrownet.com" ]; then
    CERT_DIR="/etc/letsencrypt/live/www.mygrownet.com"
    echo "✅ Found certificate at: $CERT_DIR"
else
    # List all available certificates
    echo "❌ Standard certificate not found. Available certificates:"
    ls -la /etc/letsencrypt/live/ 2>/dev/null || echo "No certificates found"
    
    # Try to find any mygrownet certificate
    CERT_DIR=$(ls -d /etc/letsencrypt/live/*mygrownet* 2>/dev/null | head -1)
    if [ -n "$CERT_DIR" ]; then
        echo "✅ Found alternative certificate at: $CERT_DIR"
    else
        echo "❌ No mygrownet.com certificates found!"
        echo ""
        echo "💡 You need to generate a wildcard SSL certificate first:"
        echo "   sudo certbot certonly --manual --preferred-challenges dns -d '*.mygrownet.com' -d 'mygrownet.com'"
        exit 1
    fi
fi

echo ""
echo "2️⃣ Checking certificate coverage..."
echo "----------------------------------"
openssl x509 -in "$CERT_DIR/fullchain.pem" -text -noout | grep -A 2 "Subject Alternative Name" || echo "Could not read certificate"

echo ""
echo "3️⃣ Creating nginx configuration..."
echo "---------------------------------"

cat > /tmp/www-redirect.conf << 'EOF'
# WWW to Non-WWW Redirect for GrowBuilder Subdomains
# Redirects www.subdomain.mygrownet.com to subdomain.mygrownet.com

server {
    listen 80;
    listen [::]:80;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com$;
    
    # Redirect to non-www
    return 301 https://$subdomain.mygrownet.com$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com$;
    
    # SSL certificate (wildcard)
    ssl_certificate CERT_DIR/fullchain.pem;
    ssl_certificate_key CERT_DIR/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
    # Redirect to non-www
    return 301 https://$subdomain.mygrownet.com$request_uri;
}
EOF

# Replace CERT_DIR with actual path
sed -i "s|CERT_DIR|$CERT_DIR|g" /tmp/www-redirect.conf

echo "✅ Configuration created with certificate path: $CERT_DIR"

echo ""
echo "4️⃣ Installing nginx configuration..."
echo "-----------------------------------"
cp /tmp/www-redirect.conf /etc/nginx/sites-available/www-redirect
ln -sf /etc/nginx/sites-available/www-redirect /etc/nginx/sites-enabled/www-redirect
echo "✅ Configuration installed"

echo ""
echo "5️⃣ Testing nginx configuration..."
echo "--------------------------------"
if nginx -t; then
    echo "✅ Nginx configuration is valid"
else
    echo "❌ Nginx configuration has errors!"
    exit 1
fi

echo ""
echo "6️⃣ Reloading nginx..."
echo "-------------------"
systemctl reload nginx
echo "✅ Nginx reloaded"

echo ""
echo "7️⃣ Testing the fix..."
echo "-------------------"
echo "Testing HTTP redirect..."
curl -I http://www.chisambofarms.mygrownet.com 2>&1 | grep -E "HTTP|Location" || echo "Could not test HTTP"

echo ""
echo "Testing HTTPS redirect..."
timeout 5 curl -I https://www.chisambofarms.mygrownet.com 2>&1 | grep -E "HTTP|Location" || echo "Could not test HTTPS (this might be normal if DNS hasn't propagated)"

echo ""
echo "✅ Fix complete!"
echo ""
echo "📋 Next steps:"
echo "1. Wait 1-2 minutes for changes to take effect"
echo "2. Test in browser: https://www.chisambofarms.mygrownet.com"
echo "3. Should redirect to: https://chisambofarms.mygrownet.com"
echo ""
echo "If still not working, the issue might be:"
echo "- DNS not propagated yet (wait 5-10 minutes)"
echo "- Wildcard certificate doesn't cover www.subdomain pattern"
echo "- Need to generate new certificate with proper coverage"
