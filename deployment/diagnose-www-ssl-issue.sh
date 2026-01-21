#!/bin/bash

# Diagnose WWW SSL Issue on Production
# Pulls latest changes and runs diagnostic

# Load credentials
source .deploy-credentials

echo "🔍 Diagnosing WWW SSL Issue on Production"
echo "=========================================="
echo ""

echo "1️⃣ Pulling latest changes..."
echo "---------------------------"
sshpass -p "$DROPLET_SUDO_PASSWORD" ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << 'ENDSSH'
cd /var/www/mygrownet.com
git pull origin main
ENDSSH

echo ""
echo "2️⃣ Running SSL diagnostic..."
echo "---------------------------"
sshpass -p "$DROPLET_SUDO_PASSWORD" ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << 'ENDSSH'
cd /var/www/mygrownet.com
sudo bash deployment/check-ssl-certificates.sh
ENDSSH

echo ""
echo "✅ Diagnostic complete!"
echo ""
echo "📋 Next steps based on diagnostic results:"
echo "1. If certificate paths are wrong → Run: bash deployment/run-www-ssl-autofix.sh"
echo "2. If certificate doesn't exist → Run: bash deployment/run-generate-wildcard-ssl.sh"
echo "3. If nginx config has errors → Check the error messages above"
