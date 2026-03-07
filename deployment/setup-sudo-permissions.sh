#!/bin/bash

# Setup sudo permissions for automated custom domain management
# This allows the web server user to run nginx and certbot commands

echo "🔐 Setting up sudo permissions for custom domain automation..."

# Create sudoers file for www-data (or sammy if that's the web user)
WEB_USER="www-data"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ This script must be run as root (use sudo)"
    exit 1
fi

# Create sudoers configuration
cat > /etc/sudoers.d/growbuilder-domains << 'EOF'
# Allow web server to manage nginx configs for custom domains
www-data ALL=(ALL) NOPASSWD: /usr/bin/tee /etc/nginx/sites-available/*
www-data ALL=(ALL) NOPASSWD: /bin/ln -sf /etc/nginx/sites-available/* /etc/nginx/sites-enabled/*
www-data ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t
www-data ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
www-data ALL=(ALL) NOPASSWD: /usr/bin/certbot --nginx *
www-data ALL=(ALL) NOPASSWD: /usr/bin/certbot revoke *
www-data ALL=(ALL) NOPASSWD: /usr/bin/certbot delete *
www-data ALL=(ALL) NOPASSWD: /bin/rm -f /etc/nginx/sites-enabled/*
www-data ALL=(ALL) NOPASSWD: /bin/rm -f /etc/nginx/sites-available/*

# Also allow sammy user (if web server runs as sammy)
sammy ALL=(ALL) NOPASSWD: /usr/bin/tee /etc/nginx/sites-available/*
sammy ALL=(ALL) NOPASSWD: /bin/ln -sf /etc/nginx/sites-available/* /etc/nginx/sites-enabled/*
sammy ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t
sammy ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
sammy ALL=(ALL) NOPASSWD: /usr/bin/certbot --nginx *
sammy ALL=(ALL) NOPASSWD: /usr/bin/certbot revoke *
sammy ALL=(ALL) NOPASSWD: /usr/bin/certbot delete *
sammy ALL=(ALL) NOPASSWD: /bin/rm -f /etc/nginx/sites-enabled/*
sammy ALL=(ALL) NOPASSWD: /bin/rm -f /etc/nginx/sites-available/*
EOF

# Set correct permissions on sudoers file
chmod 0440 /etc/sudoers.d/growbuilder-domains

# Validate sudoers syntax
if visudo -c -f /etc/sudoers.d/growbuilder-domains; then
    echo "✅ Sudo permissions configured successfully"
    echo ""
    echo "Web server can now:"
    echo "  - Create nginx configurations"
    echo "  - Test and reload nginx"
    echo "  - Request SSL certificates"
    echo "  - Remove configurations"
    echo ""
    echo "🎉 Automated custom domain system is ready!"
else
    echo "❌ Error in sudoers configuration"
    rm -f /etc/sudoers.d/growbuilder-domains
    exit 1
fi
