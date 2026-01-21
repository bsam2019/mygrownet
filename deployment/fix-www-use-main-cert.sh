#!/bin/bash

# Fix WWW Redirect to Use Main Certificate
# Run this script ON THE SERVER with sudo

if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run with sudo: sudo bash $0"
    exit 1
fi

echo "🔧 Updating WWW Redirect Configuration"
echo "======================================"
echo ""

# Update nginx config to use the main wildcard certificate
cat > /etc/nginx/sites-available/www-redirect << 'EOF'
# WWW to Non-WWW Redirect for GrowBuilder Subdomains
# Redirects www.subdomain.mygrownet.com to subdomain.mygrownet.com
# Uses main *.mygrownet.com certificate (works with Cloudflare Full mode)

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
    
    # Use main wildcard certificate
    # Cloudflare handles SSL termination in Full mode
    ssl_certificate /etc/letsencrypt/live/mygrownet.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mygrownet.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
    # Redirect to non-www
    return 301 https://$subdomain.mygrownet.com$request_uri;
}
EOF

echo "✅ Updated nginx config to use main certificate"
echo ""

# Test nginx configuration
echo "🧪 Testing nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo ""
    echo "🔄 Reloading nginx..."
    systemctl reload nginx
    echo "✅ Nginx reloaded successfully"
else
    echo ""
    echo "❌ Nginx config error!"
    exit 1
fi

# Delete the unnecessary *.www certificate
echo ""
echo "🗑️  Deleting unnecessary *.www.mygrownet.com certificate..."
if [ -d "/etc/letsencrypt/live/www.mygrownet.com" ]; then
    certbot delete --cert-name www.mygrownet.com --non-interactive
    echo "✅ Certificate deleted"
else
    echo "ℹ️  Certificate already removed or doesn't exist"
fi

echo ""
echo "✅ Complete!"
echo ""
echo "Configuration Summary:"
echo "- Nginx accepts: www.*.mygrownet.com"
echo "- Redirects to: subdomain.mygrownet.com"
echo "- Uses certificate: *.mygrownet.com"
echo "- Works with: Cloudflare Full mode"
echo ""
echo "Next steps in Cloudflare:"
echo "1. Delete the *.www DNS record (invalid for multi-level wildcards)"
echo "2. Set SSL/TLS mode to 'Full' (not Flexible, not Full Strict)"
echo "3. Test: https://www.chisambofarms.mygrownet.com"
