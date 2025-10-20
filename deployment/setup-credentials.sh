#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

CREDENTIALS_FILE="$PROJECT_ROOT/.deploy-credentials"
EXAMPLE_FILE="$PROJECT_ROOT/.deploy-credentials.example"

echo "🔐 Deployment Credentials Setup"
echo "================================"

# Check if credentials file exists
if [ -f "$CREDENTIALS_FILE" ]; then
    echo "✅ Credentials file already exists at: $CREDENTIALS_FILE"
    echo ""
    echo "Current configuration:"
    echo "---------------------"
    source "$CREDENTIALS_FILE"
    echo "Droplet IP: $DROPLET_IP"
    echo "Droplet User: $DROPLET_USER"
    echo "GitHub Username: $GITHUB_USERNAME"
    echo "Project Path: $PROJECT_PATH"
    echo ""
    read -p "Do you want to recreate the credentials file? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Keeping existing credentials file."
        exit 0
    fi
fi

# Create credentials file from example
if [ ! -f "$EXAMPLE_FILE" ]; then
    echo "❌ Error: Example file not found at $EXAMPLE_FILE"
    exit 1
fi

echo ""
echo "📝 Please enter your deployment credentials:"
echo ""

read -p "Droplet IP: " droplet_ip
read -p "Droplet User (default: sammy): " droplet_user
droplet_user=${droplet_user:-sammy}
read -sp "Droplet Sudo Password: " droplet_password
echo ""
read -p "GitHub Username: " github_username
read -sp "GitHub Token: " github_token
echo ""
read -p "Project Path (default: /var/www/mygrownet.com): " project_path
project_path=${project_path:-/var/www/mygrownet.com}

# Create credentials file
cat > "$CREDENTIALS_FILE" << EOF
# Deployment Credentials
# This file is ignored by git - DO NOT commit to repository

# Droplet SSH
DROPLET_IP="$droplet_ip"
DROPLET_USER="$droplet_user"
DROPLET_SUDO_PASSWORD="$droplet_password"

# GitHub
GITHUB_USERNAME="$github_username"
GITHUB_TOKEN="$github_token"

# Project Path
PROJECT_PATH="$project_path"
EOF

echo ""
echo "✅ Credentials file created successfully!"
echo "📁 Location: $CREDENTIALS_FILE"
echo ""
echo "You can now run deployment scripts:"
echo "  bash deployment/deploy.sh"
echo "  bash deployment/deploy-with-migration.sh"
echo "  bash deployment/deploy-with-seeder.sh"
echo "  bash deployment/deploy-with-assets.sh"
