#!/bin/bash

# Run WWW SSL Fix on Remote Server
# This script connects to the server and applies the SSL fix

# Load credentials
if [ -f .deploy-credentials ]; then
    source .deploy-credentials
else
    echo "❌ .deploy-credentials file not found!"
    exit 1
fi

echo "🔧 Applying WWW SSL Fix on Server"
echo "=================================="
echo ""

# Create a temporary script to run on the server
cat > /tmp/apply-www-ssl.sh << 'ENDSCRIPT'
#!/bin/bash

cd /var/www/mygrownet.com

echo "📥 Pulling latest code..."
git pull

echo ""
echo "📁 Finding *.www.mygrownet.com certificate..."
CERT_DIR=$(sudo ls -d /etc/letsencrypt/live/*www.mygrownet.com* 2>/dev/null | head -1)

if [ -z "$CERT_DIR" ]; then
    echo "❌ Certificate not found!"
    echo ""
    echo "Available certificates:"
    sudo ls -la /etc/letsencrypt/live/
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

# Enable the site if not already enabled
if [ ! -L /etc/nginx/sites-enabled/www-redirect ]; then
    echo "🔗 Enabling site..."
    sudo ln -s /etc/nginx/sites-available/www-redirect /etc/nginx/sites-enabled/www-redirect
fi

echo "🧪 Testing nginx configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo ""
    echo "🔄 Reloading nginx..."
    sudo systemctl reload nginx
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
    exit 1
fi
ENDSCRIPT

# Copy script to server and execute
echo "📤 Uploading script to server..."
scp -o StrictHostKeyChecking=no /tmp/apply-www-ssl.sh $DROPLET_USER@$DROPLET_IP:/tmp/

echo ""
echo "🚀 Executing on server..."
echo ""

# Execute the script on the server with sudo password
ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "echo '$DROPLET_SUDO_PASSWORD' | sudo -S bash /tmp/apply-www-ssl.sh"

# Clean up
rm /tmp/apply-www-ssl.sh
ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP "rm /tmp/apply-www-ssl.sh"

echo ""
echo "✅ Done!"
