#!/bin/bash

# Check SSL Certificates and Configuration
# This script diagnoses SSL issues with www subdomain

echo "🔍 SSL Certificate Diagnostic Tool"
echo "=================================="
echo ""

echo "1️⃣ Checking all Let's Encrypt certificates..."
echo "-------------------------------------------"
sudo certbot certificates
echo ""

echo "2️⃣ Checking certificate directory structure..."
echo "--------------------------------------------"
sudo ls -la /etc/letsencrypt/live/
echo ""

echo "3️⃣ Checking mygrownet.com certificate files..."
echo "---------------------------------------------"
if [ -d "/etc/letsencrypt/live/mygrownet.com" ]; then
    sudo ls -la /etc/letsencrypt/live/mygrownet.com/
    echo ""
    echo "Certificate details:"
    sudo openssl x509 -in /etc/letsencrypt/live/mygrownet.com/fullchain.pem -text -noout | grep -A 2 "Subject Alternative Name"
else
    echo "❌ Certificate directory not found!"
fi
echo ""

echo "4️⃣ Checking nginx www-redirect configuration..."
echo "----------------------------------------------"
if [ -f "/etc/nginx/sites-enabled/www-redirect" ]; then
    echo "✅ www-redirect config is enabled"
    echo ""
    echo "Configuration content:"
    cat /etc/nginx/sites-enabled/www-redirect
else
    echo "❌ www-redirect config not found in sites-enabled"
fi
echo ""

echo "5️⃣ Testing nginx configuration syntax..."
echo "---------------------------------------"
sudo nginx -t
echo ""

echo "6️⃣ Checking DNS resolution..."
echo "----------------------------"
echo "www.chisambofarms.mygrownet.com:"
dig www.chisambofarms.mygrownet.com +short
echo ""
echo "chisambofarms.mygrownet.com:"
dig chisambofarms.mygrownet.com +short
echo ""

echo "7️⃣ Testing SSL handshake..."
echo "--------------------------"
echo "Testing www.chisambofarms.mygrownet.com:443..."
timeout 5 openssl s_client -connect www.chisambofarms.mygrownet.com:443 -servername www.chisambofarms.mygrownet.com </dev/null 2>&1 | grep -E "subject=|issuer=|Verify return code|SSL-Session|Protocol|Cipher"
echo ""

echo "8️⃣ Checking nginx error logs (last 20 lines)..."
echo "----------------------------------------------"
sudo tail -20 /var/log/nginx/error.log
echo ""

echo "✅ Diagnostic complete!"
echo ""
echo "📋 Summary:"
echo "----------"
echo "If you see 'ERR_SSL_VERSION_OR_CIPHER_MISMATCH', possible causes:"
echo "1. SSL certificate doesn't cover www.subdomain pattern"
echo "2. Certificate paths in nginx config are incorrect"
echo "3. Wildcard certificate needs to be regenerated"
echo "4. Nginx hasn't been reloaded after config changes"
echo ""
echo "💡 Solutions:"
echo "1. Check if certificate covers *.mygrownet.com (should cover subdomains)"
echo "2. Verify certificate paths match in nginx config"
echo "3. Consider generating new wildcard cert if needed"
echo "4. Run: sudo systemctl reload nginx"
