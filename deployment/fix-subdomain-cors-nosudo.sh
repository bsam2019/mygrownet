#!/bin/bash

# Fix CORS for GrowBuilder subdomains (requires manual sudo)
# Run this on the server after SSHing in

echo "🔧 Fixing CORS for subdomains..."

# Backup current nginx config
echo "📦 Creating backup..."
sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup.$(date +%Y%m%d_%H%M%S)

# Check if CORS headers already exist
if grep -q "Access-Control-Allow-Origin" /etc/nginx/sites-available/mygrownet.com; then
    echo "⚠️  CORS headers already configured"
    echo "Checking configuration..."
    sudo nginx -t
    exit 0
fi

echo "📝 Adding CORS headers to nginx config..."

# Create a temporary file with the CORS configuration
cat > /tmp/cors-config.txt << 'EOF'
    # CORS for static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        add_header Access-Control-Allow-Origin "*" always;
        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;
        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;
        expires 1y;
        add_header Cache-Control "public, immutable";
        if ($request_method = OPTIONS) {
            return 204;
        }
    }

    # CORS for build assets
    location /build/ {
        add_header Access-Control-Allow-Origin "*" always;
        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;
        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;
        expires 1y;
        add_header Cache-Control "public, immutable";
        if ($request_method = OPTIONS) {
            return 204;
        }
    }

EOF

# Add CORS configuration before the location / block
sudo sed -i '/^[[:space:]]*location \/ {/r /tmp/cors-config.txt' /etc/nginx/sites-available/mygrownet.com

# Clean up temp file
rm /tmp/cors-config.txt

# Test nginx configuration
echo "🧪 Testing nginx configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration is valid"
    echo "🔄 Reloading nginx..."
    sudo systemctl reload nginx
    echo ""
    echo "✅ CORS fix applied successfully!"
    echo ""
    echo "🧪 Test by visiting: https://ndelimas.mygrownet.com"
    echo ""
else
    echo "❌ Nginx configuration test failed!"
    echo "Restoring backup..."
    LATEST_BACKUP=$(ls -t /etc/nginx/sites-available/mygrownet.com.backup.* | head -1)
    sudo cp $LATEST_BACKUP /etc/nginx/sites-available/mygrownet.com
    exit 1
fi
