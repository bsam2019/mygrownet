#!/bin/bash

# Fix CORS for GrowBuilder subdomains
# This adds CORS headers to nginx configuration

echo "🔧 Fixing CORS for subdomains..."

# Backup current nginx config
sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup.$(date +%Y%m%d_%H%M%S)

# Check if CORS headers already exist
if grep -q "Access-Control-Allow-Origin" /etc/nginx/sites-available/mygrownet.com; then
    echo "⚠️  CORS headers already configured"
else
    echo "📝 Adding CORS headers to nginx config..."
    
    # Add CORS configuration before the closing brace of the server block
    sudo sed -i '/^[[:space:]]*location \/ {/i\
    # CORS for static assets\
    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {\
        add_header Access-Control-Allow-Origin "*" always;\
        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;\
        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;\
        expires 1y;\
        add_header Cache-Control "public, immutable";\
        if ($request_method = OPTIONS) {\
            return 204;\
        }\
    }\
    \
    # CORS for build assets\
    location /build/ {\
        add_header Access-Control-Allow-Origin "*" always;\
        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;\
        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;\
        expires 1y;\
        add_header Cache-Control "public, immutable";\
        if ($request_method = OPTIONS) {\
            return 204;\
        }\
    }\
' /etc/nginx/sites-available/mygrownet.com
fi

# Test nginx configuration
echo "🧪 Testing nginx configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration is valid"
    echo "🔄 Reloading nginx..."
    sudo systemctl reload nginx
    echo "✅ CORS fix applied successfully!"
    echo ""
    echo "Test by visiting: https://ndelimas.mygrownet.com"
else
    echo "❌ Nginx configuration test failed!"
    echo "Restoring backup..."
    sudo cp /etc/nginx/sites-available/mygrownet.com.backup.$(date +%Y%m%d_%H%M%S) /etc/nginx/sites-available/mygrownet.com
    exit 1
fi
