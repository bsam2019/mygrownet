#!/bin/bash

# Fix WWW SSL Certificate for GrowBuilder Subdomains
# This script ensures www.subdomain.mygrownet.com works with SSL

set -e

echo "🔒 Fixing WWW SSL Certificate..."

# Load credentials
source .deploy-credentials

# The issue: www.chisambofarms.mygrownet.com needs SSL
# Solution: The wildcard cert *.mygrownet.com should cover it, but we need to ensure it's properly configured

ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
    echo "📋 Checking current SSL certificates..."
    sudo certbot certificates
    
    echo ""
    echo "🔍 The wildcard certificate *.mygrownet.com should cover:"
    echo "  - chisambofarms.mygrownet.com ✓"
    echo "  - www.chisambofarms.mygrownet.com ✓"
    echo ""
    
    echo "💡 If the wildcard cert exists but www still fails, the issue is likely:"
    echo "  1. Nginx configuration not handling www subdomain"
    echo "  2. DNS not pointing www.chisambofarms to the server"
    echo ""
    
    echo "🔧 Adding Nginx redirect for www subdomains..."
    
    # Create nginx config snippet for www redirect
    sudo tee /etc/nginx/snippets/www-redirect.conf > /dev/null << 'EOF'
# Redirect www.subdomain.mygrownet.com to subdomain.mygrownet.com
server {
    listen 80;
    listen 443 ssl http2;
    server_name ~^www\.(?<subdomain>.+)\.mygrownet\.com$;
    
    # SSL certificate (wildcard)
    ssl_certificate /etc/letsencrypt/live/mygrownet.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mygrownet.com/privkey.pem;
    
    # Redirect to non-www
    return 301 https://$subdomain.mygrownet.com$request_uri;
}
EOF
    
    echo "✅ Created www redirect configuration"
    
    echo ""
    echo "📝 To apply this fix, you need to:"
    echo "  1. Include this snippet in your main nginx config"
    echo "  2. Add it before the main subdomain server block"
    echo "  3. Test with: sudo nginx -t"
    echo "  4. Reload with: sudo systemctl reload nginx"
    echo ""
    echo "Or run the complete setup script that includes this fix."
ENDSSH

echo ""
echo "✅ WWW SSL fix prepared!"
echo ""
echo "📌 Next steps:"
echo "  1. The script created /etc/nginx/snippets/www-redirect.conf"
echo "  2. You need to include it in your nginx config"
echo "  3. Or better: ensure your wildcard SSL cert is properly configured"
echo ""
echo "🔍 To check DNS:"
echo "  dig www.chisambofarms.mygrownet.com"
echo ""
echo "🔍 To check SSL:"
echo "  openssl s_client -connect www.chisambofarms.mygrownet.com:443 -servername www.chisambofarms.mygrownet.com"
