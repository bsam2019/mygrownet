#!/bin/bash

# Pull changes and run SSL diagnostic on droplet

# Load credentials
source .deploy-credentials

echo "🔍 Pull and Diagnose SSL on Droplet"
echo "===================================="
echo ""

echo "Connecting to droplet..."
ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH

echo "📥 Pulling latest changes..."
cd /var/www/mygrownet.com
git pull origin main

echo ""
echo "🔍 Running SSL diagnostic..."
echo "============================"
echo "$DROPLET_SUDO_PASSWORD" | sudo -S bash deployment/check-ssl-certificates.sh

ENDSSH

echo ""
echo "✅ Done!"
