#!/bin/bash

# Interactive SSL Certificate Setup for *.www.mygrownet.com

# Load credentials
source .deploy-credentials

echo "🔐 Setting up SSL Certificate for *.www.mygrownet.com"
echo "====================================================="
echo ""
echo "This will:"
echo "1. Connect to your server"
echo "2. Run certbot to generate certificate"
echo "3. You'll need to add a TXT record to Cloudflare DNS"
echo ""
echo "Press Enter to continue..."
read

echo "Connecting to server..."
echo ""

ssh -t $DROPLET_USER@$DROPLET_IP << 'ENDSSH'

echo "🔐 Generating SSL Certificate for *.www.mygrownet.com"
echo "====================================================="
echo ""

# Run certbot with DNS challenge
sudo certbot certonly \
    --manual \
    --preferred-challenges dns \
    --email admin@mygrownet.com \
    --agree-tos \
    --no-eff-email \
    -d '*.www.mygrownet.com'

echo ""
echo "✅ Certificate generation complete!"
echo ""

# Show certificate location
echo "📁 Certificate location:"
sudo ls -la /etc/letsencrypt/live/ | grep www

echo ""
echo "Press Enter to continue..."
read

ENDSSH

echo ""
echo "✅ Certificate generated!"
echo ""
echo "📋 Next step:"
echo "Run: bash deployment/update-www-nginx-with-ssl.sh"
