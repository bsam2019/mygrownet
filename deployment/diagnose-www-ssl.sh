#!/bin/bash

# Diagnose WWW SSL Issue
# Checks SSL certificates and nginx configuration

echo "🔍 Diagnosing WWW SSL Issue..."
echo ""

echo "1️⃣ Checking SSL certificates..."
sudo certbot certificates

echo ""
echo "2️⃣ Checking if www-redirect config exists..."
ls -la /etc/nginx/sites-enabled/ | grep www-redirect

echo ""
echo "3️⃣ Checking nginx configuration syntax..."
sudo nginx -t

echo ""
echo "4️⃣ Checking SSL certificate files..."
ls -la /etc/letsencrypt/live/mygrownet.com/

echo ""
echo "5️⃣ Testing SSL connection to www subdomain..."
echo "Q" | openssl s_client -connect www.chisambofarms.mygrownet.com:443 -servername www.chisambofarms.mygrownet.com 2>&1 | grep -E "subject=|issuer=|Verify return code"

echo ""
echo "6️⃣ Checking DNS resolution..."
dig www.chisambofarms.mygrownet.com +short

echo ""
echo "✅ Diagnosis complete!"
