#!/bin/bash

# Update nginx cache configuration for MyGrowNet

echo "Updating nginx cache configuration..."

# Backup current config
cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup

# Remove old cache rule
sed -i '/location ~\* \\.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)\$ {/,/}/d' /etc/nginx/sites-available/mygrownet.com

# Add new cache rules before the last closing brace
sed -i '/^}$/i\
\
    # Dont cache HTML files - always fetch fresh\
    location ~* \\.html$ {\
        expires -1;\
        add_header Cache-Control "no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0";\
    }\
\
    # Cache build assets (versioned by Vite) for 1 year\
    location ~* /build/.*\\.(js|css)$ {\
        expires 1y;\
        add_header Cache-Control "public, immutable";\
    }\
\
    # Cache images and fonts for 1 month\
    location ~* \\.(jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot|webp)$ {\
        expires 30d;\
        add_header Cache-Control "public";\
    }\
\
    # Dont cache service worker\
    location = /sw.js {\
        expires -1;\
        add_header Cache-Control "no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0";\
    }' /etc/nginx/sites-available/mygrownet.com

# Test nginx config
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx config is valid"
    echo "Reloading nginx..."
    systemctl reload nginx
    echo "✅ Nginx reloaded successfully"
else
    echo "❌ Nginx config test failed, restoring backup"
    cp /etc/nginx/sites-available/mygrownet.com.backup /etc/nginx/sites-available/mygrownet.com
    exit 1
fi
