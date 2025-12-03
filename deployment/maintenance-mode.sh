#!/bin/bash

# Maintenance Mode Management Script
# Usage: ./deployment/maintenance-mode.sh [enable|disable|status] [--secret=YOUR_SECRET]

# Parse arguments
COMMAND="${1:-enable}"
SECRET=""

for arg in "$@"; do
    case $arg in
        --secret=*)
            SECRET="${arg#*=}"
            shift
            ;;
        enable|disable|status)
            COMMAND="$arg"
            shift
            ;;
    esac
done

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

# Function to enable maintenance mode
enable_maintenance() {
    echo "🔧 Enabling maintenance mode..."
    echo "📍 Server: $DROPLET_IP"

    # Pass secret to remote server if provided
    if [ -n "$SECRET" ]; then
        ssh ${DROPLET_USER}@${DROPLET_IP} bash -s "$SECRET" << 'ENDSSH'
SECRET_TOKEN="$1"
cd /var/www/mygrownet.com

# Enable maintenance mode with custom secret
php artisan down \
    --retry=60 \
    --secret="$SECRET_TOKEN" \
    --render="errors::503" \
    --status=503

echo "✅ Maintenance mode enabled with custom secret"
echo ""
echo "🔑 Admin Bypass URL:"
echo "   https://mygrownet.com/$SECRET_TOKEN"
echo ""
echo "💡 Visit this URL to bypass maintenance mode as admin"
ENDSSH
    else
        ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

# Generate a random secret token
SECRET_TOKEN=$(openssl rand -hex 20)

# Enable maintenance mode with auto-generated secret
php artisan down \
    --retry=60 \
    --secret="$SECRET_TOKEN" \
    --render="errors::503" \
    --status=503

echo "✅ Maintenance mode enabled"
echo ""
echo "🔑 Admin Bypass URL:"
echo "   https://mygrownet.com/$SECRET_TOKEN"
echo ""
echo "💡 Visit this URL to bypass maintenance mode as admin"
echo "   (Keep this URL private!)"
ENDSSH
    fi

    echo ""
    echo "🎉 Maintenance mode enabled!"
}

# Function to disable maintenance mode
disable_maintenance() {
    echo "🔧 Disabling maintenance mode..."
    echo "📍 Server: $DROPLET_IP"

    ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

php artisan up

echo "✅ Maintenance mode disabled"
echo "🌐 Site is now live!"
ENDSSH

    echo ""
    echo "🎉 Site is back online!"
}

# Function to check maintenance status
check_status() {
    echo "🔍 Checking maintenance mode status..."
    echo "📍 Server: $DROPLET_IP"

    ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

if [ -f storage/framework/down ]; then
    echo "🔴 Maintenance mode: ENABLED"
    echo ""
    echo "📄 Maintenance file contents:"
    cat storage/framework/down | jq '.' 2>/dev/null || cat storage/framework/down
else
    echo "🟢 Maintenance mode: DISABLED"
    echo "🌐 Site is live"
fi
ENDSSH
}

# Execute command
case $COMMAND in
    enable)
        enable_maintenance
        ;;
    disable)
        disable_maintenance
        ;;
    status)
        check_status
        ;;
    *)
        echo "Usage: $0 [enable|disable|status] [--secret=YOUR_SECRET]"
        echo ""
        echo "Commands:"
        echo "  enable   - Enable maintenance mode"
        echo "  disable  - Disable maintenance mode"
        echo "  status   - Check maintenance mode status"
        echo ""
        echo "Options:"
        echo "  --secret=YOUR_SECRET  - Set a custom secret token to bypass maintenance mode"
        echo ""
        echo "Examples:"
        echo "  $0 enable                      # Auto-generate random secret"
        echo "  $0 enable --secret=adminbypass # Use custom secret"
        echo "  $0 disable                     # Disable maintenance mode"
        echo "  $0 status                      # Check current status"
        exit 1
        ;;
esac
