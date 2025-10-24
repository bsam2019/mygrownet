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
    exit 1
fi

echo "🔄 Pulling latest changes from GitHub to droplet..."
echo "📍 Server: $DROPLET_IP"

# SSH and run git pull commands
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

echo "📂 Current directory: \$(pwd)"
echo "🔍 Git status before pull:"
git status --short

# Remove untracked files that might conflict
echo ""
echo "🗑️  Removing conflicting files..."
rm -f routes/debug.php

# Fix permissions before git operations
echo ""
echo "🔓 Fixing file permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R ${DROPLET_USER}:${DROPLET_USER} .
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R u+w .

# Reset any local changes
echo ""
echo "🔄 Resetting local changes..."
git reset --hard HEAD

# Pull latest changes
echo ""
echo "📥 Pulling from GitHub..."
git pull https://${GITHUB_USERNAME}:${GITHUB_TOKEN}@github.com/${GITHUB_USERNAME}/mygrownet.git main

echo ""
echo "✅ Git pull complete!"
echo ""
echo "🔍 Git status after pull:"
git status --short

# Clear caches
echo ""
echo "🧹 Clearing Laravel caches..."
php artisan optimize:clear

# Optimize
echo ""
echo "🚀 Optimizing Laravel..."
php artisan optimize

echo ""
echo "✅ All done!"

ENDSSH

echo "🎉 Changes pulled successfully!"
