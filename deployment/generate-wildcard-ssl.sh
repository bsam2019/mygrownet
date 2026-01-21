#!/bin/bash

# Generate Wildcard SSL Certificate for MyGrowNet
# This creates a certificate that covers *.mygrownet.com

set -e

echo "🔐 Wildcard SSL Certificate Generator"
echo "====================================="
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run with sudo"
    exit 1
fi

echo "⚠️  IMPORTANT: This will use DNS challenge method"
echo "You will need to add TXT records to your DNS"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled"
    exit 0
fi

echo ""
echo "1️⃣ Generating wildcard certificate..."
echo "------------------------------------"
echo ""
echo "This certificate will cover:"
echo "  - mygrownet.com"
echo "  - *.mygrownet.com (all subdomains)"
echo "  - www.*.mygrownet.com (www + subdomains) - if supported"
echo ""

# Generate certificate with DNS challenge
certbot certonly \
    --manual \
    --preferred-challenges dns \
    --email admin@mygrownet.com \
    --agree-tos \
    --no-eff-email \
    -d 'mygrownet.com' \
    -d '*.mygrownet.com'

echo ""
echo "✅ Certificate generation complete!"
echo ""

echo "2️⃣ Verifying certificate..."
echo "--------------------------"
if [ -d "/etc/letsencrypt/live/mygrownet.com" ]; then
    echo "✅ Certificate directory exists"
    echo ""
    echo "Certificate details:"
    openssl x509 -in /etc/letsencrypt/live/mygrownet.com/fullchain.pem -text -noout | grep -A 2 "Subject Alternative Name"
else
    echo "❌ Certificate directory not found!"
    exit 1
fi

echo ""
echo "3️⃣ Next steps:"
echo "-------------"
echo "1. Run the auto-fix script:"
echo "   sudo bash deployment/fix-www-ssl-auto.sh"
echo ""
echo "2. Or manually update nginx configs to use the new certificate"
echo ""
echo "📝 Note: Wildcard certificates typically cover:"
echo "   ✅ subdomain.mygrownet.com"
echo "   ❌ www.subdomain.mygrownet.com (two-level subdomain)"
echo ""
echo "For www.subdomain support, you need to redirect (which is what we're doing)"
