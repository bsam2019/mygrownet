#!/bin/bash

# Run WWW SSL Auto-Fix on Production
# Automatically fixes SSL certificate configuration

# Load credentials
source .deploy-credentials

echo "🔧 Running WWW SSL Auto-Fix on Production"
echo "=========================================="
echo ""

echo "This will:"
echo "- Find the correct SSL certificate"
echo "- Update nginx configuration"
echo "- Reload nginx"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled"
    exit 0
fi

echo ""
echo "Running auto-fix script on server..."
echo "-----------------------------------"
sshpass -p "$DROPLET_SUDO_PASSWORD" ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH
cd /var/www/mygrownet.com
echo "$DROPLET_SUDO_PASSWORD" | sudo -S bash deployment/fix-www-ssl-auto.sh
ENDSSH

echo ""
echo "✅ Auto-fix complete!"
echo ""
echo "🧪 Testing the fix..."
echo "-------------------"
echo "HTTP redirect test:"
curl -I http://www.chisambofarms.mygrownet.com 2>&1 | grep -E "HTTP|Location" || echo "Could not test"

echo ""
echo "HTTPS redirect test:"
timeout 5 curl -I https://www.chisambofarms.mygrownet.com 2>&1 | grep -E "HTTP|Location" || echo "Could not test (might need DNS propagation)"

echo ""
echo "📝 Please test in browser:"
echo "https://www.chisambofarms.mygrownet.com"
echo ""
echo "Should redirect to:"
echo "https://chisambofarms.mygrownet.com"
