#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Load credentials from .deploy-credentials file
if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    exit 1
fi

echo "🚀 Setting up Redis on production server..."
echo "📍 Server: $DROPLET_IP"

# SSH and run Redis setup
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

echo "📦 Installing Redis..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S apt update
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S apt install -y redis-server

echo "⚙️ Configuring Redis..."
# Enable Redis to start on boot
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S systemctl enable redis-server

# Start Redis
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S systemctl start redis-server

# Test Redis
echo "🧪 Testing Redis connection..."
redis-cli ping

echo "✅ Redis setup complete!"

ENDSSH

echo "🎉 Redis installed on production!"
