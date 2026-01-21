#!/bin/bash

# Generate Wildcard SSL Certificate on Production
# Interactive script - requires DNS TXT record setup

# Load credentials
source .deploy-credentials

echo "🔐 Generate Wildcard SSL Certificate on Production"
echo "=================================================="
echo ""

echo "⚠️  IMPORTANT:"
echo "This will use DNS challenge method."
echo "You will need to add TXT records to Cloudflare DNS."
echo ""
echo "The process will:"
echo "1. Start certificate generation"
echo "2. Show you TXT records to add to DNS"
echo "3. Wait for you to add them"
echo "4. Verify and complete certificate generation"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled"
    exit 0
fi

echo ""
echo "Connecting to server..."
echo "---------------------"
sshpass -p "$DROPLET_SUDO_PASSWORD" ssh -t -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH
cd /var/www/mygrownet.com
echo "$DROPLET_SUDO_PASSWORD" | sudo -S bash deployment/generate-wildcard-ssl.sh
ENDSSH

echo ""
echo "✅ Certificate generation complete!"
echo ""
echo "📋 Next step:"
echo "Run the auto-fix script to configure nginx:"
echo "bash deployment/run-www-ssl-autofix.sh"
