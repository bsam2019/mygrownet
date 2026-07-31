#!/bin/bash

# Deploy script for subdomain fixes (GrowFinance, BMS, StockFlow logout)
# Run this on production server after git pull

echo "========================================="
echo "Deploying Subdomain Fixes"
echo "========================================="

# Clear all caches
echo "Clearing caches..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild caches
echo "Rebuilding caches..."
php artisan route:cache
php artisan config:cache
php artisan optimize

# Run seeder to update domain records (if needed)
echo "Updating domain records..."
php artisan db:seed --class=ApplicationDomainsSeeder

echo "========================================="
echo "Deployment Complete!"
echo "========================================="
echo ""
echo "Testing URLs:"
echo "- BMS: https://bms.mygrownet.com/"
echo "- GrowFinance: https://growfinance.mygrownet.com/"
echo "- StockFlow: https://taradasi.mygrownet.com/ (or your company subdomain)"
echo ""
echo "If BMS still shows 404:"
echo "1. Check DNS: dig bms.mygrownet.com"
echo "2. Verify route cache: php artisan route:list | grep 'bms.subdomain'"
echo "3. Check DetectSubdomain middleware is loaded"
echo ""
