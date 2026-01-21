#!/bin/bash

# Generate Let's Encrypt Wildcard SSL for *.www.mygrownet.com
# This covers www.subdomain.mygrownet.com pattern

# Load credentials
source .deploy-credentials

echo "🔐 Generate Wildcard SSL for *.www.mygrownet.com"
echo "================================================"
echo ""

echo "This will generate a certificate covering:"
echo "  - *.www.mygrownet.com (www.subdomain.mygrownet.com)"
echo ""
echo "⚠️  IMPORTANT:"
echo "- Uses DNS challenge (you'll need to add TXT record to Cloudflare)"
echo "- This is a SEPARATE certificate from *.mygrownet.com"
echo ""

read -p "Continue? (y/n) " -n 1 -r
echo ""
if [[ ! \$REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled"
    exit 0
fi

ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH

echo "📝 Generating certificate for *.www.mygrownet.com..."
echo ""

echo "$DROPLET_SUDO_PASSWORD" | sudo -S certbot certonly \\
    --manual \\
    --preferred-challenges dns \\
    --email admin@mygrownet.com \\
    --agree-tos \\
    --no-eff-email \\
    -d '*.www.mygrownet.com'

echo ""
echo "✅ Certificate generation complete!"
echo ""

echo "📁 Checking certificate location..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S ls -la /etc/letsencrypt/live/

ENDSSH

echo ""
echo "✅ Done!"
echo ""
echo "📋 Next steps:"
echo "1. Update nginx config to use the new certificate"
echo "2. Run: bash deployment/update-www-nginx-with-ssl.sh"
