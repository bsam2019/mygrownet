#!/bin/bash

# Fix WWW redirect - HTTP only (no SSL needed)
# Since *.www is not covered by SSL certificate, we redirect at HTTP level only

# Load credentials
source .deploy-credentials

echo "🔧 Fixing WWW Redirect (HTTP Only)"
echo "==================================="
echo ""

echo "Strategy: Redirect www.subdomain at HTTP level only"
echo "Users visiting https://www.subdomain will get browser warning"
echo "But http://www.subdomain will redirect properly"
echo ""

ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH

cd /var/www/mygrownet.com

echo "📝 Updating www-redirect nginx config (HTTP only)..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S tee /etc/nginx/sites-available/www-redirect > /dev/null << 'EOF'
# WWW to Non-WWW Redirect for GrowBuilder Subdomains
# Redirects www.subdomain.mygrownet.com to subdomain.mygrownet.com
# HTTP only - no SSL for www.subdomain pattern

server {
    listen 80;
    listen [::]:80;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com$;
    
    # Redirect to non-www (HTTPS)
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}

# Note: No HTTPS server block for www.subdomain pattern
# SSL certificate *.mygrownet.com doesn't cover www.subdomain.mygrownet.com
# Users should use subdomain.mygrownet.com directly
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
echo "📝 Result:"
echo "- http://www.chisambofarms.mygrownet.com → redirects to https://chisambofarms.mygrownet.com ✅"
echo "- https://www.chisambofarms.mygrownet.com → SSL error (expected, no certificate) ⚠️"
echo ""
echo "💡 Recommendation: Don't promote www.subdomain URLs"
echo "   Always use: subdomain.mygrownet.com (without www)"
