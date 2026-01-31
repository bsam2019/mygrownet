#!/bin/bash

# Setup flamesofhopechurch.com custom domain
# Run this script directly on the server as: sudo ./setup-flamesofhope.sh

echo "🚀 Setting up flamesofhopechurch.com..."

# Step 1: Copy nginx config
echo "📝 Copying nginx configuration..."
cp /tmp/flamesofhopechurch.com.conf /etc/nginx/sites-available/flamesofhopechurch.com
echo "✅ Config copied"

# Step 2: Enable site
echo "🔗 Enabling site..."
ln -sf /etc/nginx/sites-available/flamesofhopechurch.com /etc/nginx/sites-enabled/
echo "✅ Site enabled"

# Step 3: Test nginx config
echo "🧪 Testing nginx configuration..."
nginx -t
if [ $? -ne 0 ]; then
    echo "❌ Nginx configuration test failed"
    exit 1
fi
echo "✅ Nginx config is valid"

# Step 4: Reload nginx
echo "🔄 Reloading nginx..."
systemctl reload nginx
echo "✅ Nginx reloaded"

# Step 5: Test HTTP
echo "🌐 Testing HTTP connection..."
sleep 2
curl -I http://flamesofhopechurch.com 2>&1 | head -5

# Step 6: Setup SSL
echo ""
echo "🔒 Setting up SSL certificate..."
echo "This may take a few minutes..."
certbot --nginx -d flamesofhopechurch.com -d www.flamesofhopechurch.com --non-interactive --agree-tos --email support@mygrownet.com --redirect

if [ $? -eq 0 ]; then
    echo "✅ SSL certificate installed successfully"
else
    echo "⚠️  SSL certificate installation failed"
    echo "This might be because:"
    echo "  1. DNS is still propagating"
    echo "  2. Cloudflare proxy is enabled (should be DNS only)"
    echo ""
    echo "To retry manually:"
    echo "  sudo certbot --nginx -d flamesofhopechurch.com -d www.flamesofhopechurch.com"
fi

echo ""
echo "🎉 Setup complete!"
echo ""
echo "Test your site:"
echo "  http://flamesofhopechurch.com"
echo "  https://flamesofhopechurch.com"
echo "  https://www.flamesofhopechurch.com"
