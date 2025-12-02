#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Load credentials from .deploy-credentials file
if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    echo "Please create .deploy-credentials file in project root."
    echo "Copy from .deploy-credentials.example and fill in your credentials."
    exit 1
fi

# Check if user_id argument is provided
if [ -z "$1" ]; then
    echo "❌ Error: User ID is required!"
    echo ""
    echo "Usage: ./deployment/reset-user-session.sh <user_id>"
    echo "Example: ./deployment/reset-user-session.sh 123"
    exit 1
fi

USER_ID=$1

echo "🔄 Resetting user session on production..."
echo "📍 Server: $DROPLET_IP"
echo "👤 User ID: $USER_ID"
echo ""

# SSH and run the session reset command
ssh ${DROPLET_USER}@${DROPLET_IP} "cd ${PROJECT_PATH} && php artisan user:reset-session ${USER_ID}"

echo ""
echo "🎉 Done! User $USER_ID will need to log in again on production."
