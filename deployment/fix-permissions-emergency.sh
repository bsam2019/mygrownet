#!/bin/bash

# Emergency Permission Fix

set -e

if [ -f .deploy-credentials ]; then
    source .deploy-credentials
else
    echo "Error: .deploy-credentials file not found"
    exit 1
fi

echo "Fixing permissions on server..."

ssh -t $DROPLET_USER@$DROPLET_IP << 'ENDSSH'
    cd /var/www/mygrownet.com
    
    echo "Fixing storage permissions..."
    sudo chown -R www-data:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache
    
    echo "Clearing compiled views..."
    sudo rm -rf storage/framework/views/*
    
    echo "Permissions fixed!"
ENDSSH

echo "Site should be back online now"
