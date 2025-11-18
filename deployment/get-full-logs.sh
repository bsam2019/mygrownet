#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Load credentials
if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    exit 1
fi

echo "📋 Getting detailed error logs..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

echo "=== LATEST 500 ERRORS FROM LARAVEL LOG ==="
grep -B 5 -A 50 "production.ERROR" storage/logs/laravel.log | tail -300

echo ""
echo "=== PHP-FPM ERROR LOG ==="
sudo tail -100 /var/log/php*-fpm.log 2>/dev/null || echo "No PHP-FPM log found"

echo ""
echo "=== NGINX ERROR LOG ==="
sudo tail -100 /var/log/nginx/error.log 2>/dev/null || echo "No Nginx error log found"

echo ""
echo "=== CHECKING PERMISSIONS ==="
ls -la storage/logs/
ls -la bootstrap/cache/

echo ""
echo "=== CHECKING PHP VERSION ==="
php -v

echo ""
echo "=== CHECKING COMPOSER AUTOLOAD ==="
ls -la vendor/autoload.php

ENDSSH
