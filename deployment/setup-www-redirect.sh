#!/bin/bash

# Setup WWW to Non-WWW Redirect for GrowBuilder Subdomains
# This ensures www.subdomain.mygrownet.com redirects to subdomain.mygrownet.com

set -e

echo "🔄 Setting up WWW to Non-WWW redirect for GrowBuilder subdomains..."

# Load credentials
source .deploy-credentials

ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
    cd /var/www/mygrownet.com
    
    echo "📋 Step 1: Copy nginx configuration..."
    sudo cp deployment/nginx-www-redirect.conf /etc/nginx/sites-available/www-redirect
    
    echo "🔗 Step 2: Enable the configuration..."
    sudo ln -sf /etc/nginx/sites-available/www-redirect /etc/nginx/sites-enabled/www-redirect
    
    echo "✅ Step 3: Test nginx configuration..."
    sudo nginx -t
    
    if [ $? -eq 0 ]; then
        echo "🔄 Step 4: Reload nginx..."
        sudo systemctl reload nginx
        echo "✅ Nginx reloaded successfully!"
    else
        echo "❌ Nginx configuration test failed!"
        exit 1
    fi
    
    echo ""
    echo "✅ WWW redirect setup complete!"
    echo ""
    echo "📝 What this does:"
    echo "  - www.chisambofarms.mygrownet.com → chisambofarms.mygrownet.com"
    echo "  - www.anysite.mygrownet.com → anysite.mygrownet.com"
    echo ""
    echo "🔒 SSL: Uses the wildcard certificate *.mygrownet.com"
    echo ""
    echo "🧪 Test it:"
    echo "  curl -I https://www.chisambofarms.mygrownet.com"
    echo "  (Should show 301 redirect to https://chisambofarms.mygrownet.com)"
ENDSSH

echo ""
echo "🎉 Done! WWW redirect is now active."
echo ""
echo "Test URLs:"
echo "  https://www.chisambofarms.mygrownet.com (should redirect)"
echo "  https://chisambofarms.mygrownet.com (should work)"
