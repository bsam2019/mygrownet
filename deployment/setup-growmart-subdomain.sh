#!/bin/bash

# GrowMart Subdomain Setup Script
# Sets up growmart.mygrownet.com subdomain on DigitalOcean droplet
# Uses existing wildcard SSL certificate

set -e

echo "=================================================="
echo "GrowMart Subdomain Setup"
echo "=================================================="
echo ""

# Load deployment credentials
if [ -f .deploy-credentials ]; then
    source .deploy-credentials
else
    echo "Error: .deploy-credentials file not found!"
    echo "Please create it using setup-credentials.sh"
    exit 1
fi

echo "Target Server: $DROPLET_IP"
echo "Subdomain: growmart.mygrownet.com"
echo ""

# Step 1: Upload nginx configuration
echo "Step 1: Uploading nginx configuration..."
scp deployment/growmart-subdomain.conf ${DROPLET_USER}@${DROPLET_IP}:/tmp/growmart-subdomain.conf
echo "✓ Configuration uploaded"
echo ""

# Step 2: Configure nginx on server
echo "Step 2: Configuring nginx..."
ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
    # Move config to nginx sites-available
    echo $DROPLET_SUDO_PASSWORD | sudo -S mv /tmp/growmart-subdomain.conf /etc/nginx/sites-available/growmart-subdomain.conf
    
    # Create symlink if it doesn't exist
    if [ ! -f /etc/nginx/sites-enabled/growmart-subdomain.conf ]; then
        echo $DROPLET_SUDO_PASSWORD | sudo -S ln -s /etc/nginx/sites-available/growmart-subdomain.conf /etc/nginx/sites-enabled/
        echo "✓ Symlink created"
    else
        echo "✓ Symlink already exists"
    fi
    
    # Test nginx configuration
    echo $DROPLET_SUDO_PASSWORD | sudo -S nginx -t
    
    if [ $? -eq 0 ]; then
        echo "✓ Nginx configuration is valid"
    else
        echo "✗ Nginx configuration test failed!"
        exit 1
    fi
ENDSSH

echo "✓ Nginx configured successfully"
echo ""

# Step 3: Reload nginx
echo "Step 3: Reloading nginx..."
ssh ${DROPLET_USER}@${DROPLET_IP} "echo $DROPLET_SUDO_PASSWORD | sudo -S systemctl reload nginx"
echo "✓ Nginx reloaded"
echo ""

# Step 4: Update Laravel configuration
echo "Step 4: Updating Laravel configuration..."
ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
    cd /var/www/mygrownet.com
    
    # Clear caches
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Cache optimized configuration
    php artisan config:cache
    php artisan route:cache
    
    echo "✓ Laravel caches cleared and rebuilt"
ENDSSH

echo "✓ Laravel configuration updated"
echo ""

# Step 5: Test subdomain
echo "Step 5: Testing subdomain..."
echo ""
echo "Testing HTTP redirect..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://growmart.mygrownet.com)
if [ "$HTTP_STATUS" == "301" ] || [ "$HTTP_STATUS" == "302" ]; then
    echo "✓ HTTP redirects to HTTPS"
else
    echo "⚠ HTTP status: $HTTP_STATUS (expected 301 or 302)"
fi
echo ""

echo "Testing HTTPS..."
HTTPS_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://growmart.mygrownet.com)
if [ "$HTTPS_STATUS" == "200" ]; then
    echo "✓ HTTPS is working"
else
    echo "⚠ HTTPS status: $HTTPS_STATUS (expected 200)"
fi
echo ""

# Step 6: Check SSL certificate
echo "Step 6: Verifying SSL certificate..."
SSL_INFO=$(echo | openssl s_client -servername growmart.mygrownet.com -connect growmart.mygrownet.com:443 2>/dev/null | openssl x509 -noout -subject -dates 2>/dev/null)
if [ $? -eq 0 ]; then
    echo "✓ SSL certificate is valid"
    echo "$SSL_INFO"
else
    echo "⚠ Could not verify SSL certificate"
fi
echo ""

# Summary
echo "=================================================="
echo "Setup Complete!"
echo "=================================================="
echo ""
echo "GrowMart subdomain is now configured at:"
echo "  https://growmart.mygrownet.com"
echo ""
echo "Next steps:"
echo "  1. Add DNS record in Cloudflare:"
echo "     Type: CNAME"
echo "     Name: growmart"
echo "     Target: mygrownet.com"
echo "     Proxy: Enabled (orange cloud)"
echo ""
echo "  2. Wait 5-30 minutes for DNS propagation"
echo ""
echo "  3. Test the subdomain:"
echo "     curl -I https://growmart.mygrownet.com"
echo ""
echo "  4. Access admin panel:"
echo "     https://growmart.mygrownet.com/admin"
echo ""
echo "Configuration files:"
echo "  - Nginx: /etc/nginx/sites-available/growmart-subdomain.conf"
echo "  - Logs: /var/log/nginx/growmart-*.log"
echo ""
echo "To view logs:"
echo "  ssh ${DROPLET_USER}@${DROPLET_IP}"
echo "  sudo tail -f /var/log/nginx/growmart-error.log"
echo "  sudo tail -f /var/log/nginx/growmart-access.log"
echo ""
