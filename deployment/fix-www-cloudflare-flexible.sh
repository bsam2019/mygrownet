#!/bin/bash

# Fix WWW redirect for Cloudflare Flexible SSL
# Removes HTTPS server block since Cloudflare handles SSL

# Load credentials
source .deploy-credentials

echo "🔧 Fixing WWW Redirect for Cloudflare Flexible SSL"
echo "=================================================="
echo ""

echo "Strategy:"
echo "- Remove HTTPS server block (Cloudflare handles SSL)"
echo "- Keep HTTP redirect only"
echo "- Cloudflare (HTTPS) → Your Server (HTTP redirect)"
echo ""

ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH

cd /var/www/mygrownet.com

echo "📝 Updating www-redirect nginx config..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S tee /etc/nginx/sites-available/www-redirect > /dev/null << 'EOF'
# WWW to Non-WWW Redirect for GrowBuilder Subdomains
# Works with Cloudflare Flexible SSL
# Cloudflare handles HTTPS, server only does HTTP redirect

server {
    listen 80;
    listen [::]:80;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com$;
    
    # Redirect to non-www (HTTPS)
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}

# No HTTPS server block needed - Cloudflare handles SSL
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
echo "📝 How it works:"
echo "1. User visits: https://www.chisambofarms.mygrownet.com"
echo "2. Cloudflare terminates SSL (shows valid certificate)"
echo "3. Cloudflare connects to your server via HTTP"
echo "4. Your server redirects to: https://chisambofarms.mygrownet.com"
echo "5. User sees the redirect (no SSL error)"
echo ""
echo "🧪 Test in browser:"
echo "https://www.chisambofarms.mygrownet.com"
