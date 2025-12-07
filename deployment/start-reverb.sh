#!/bin/bash

# Simple script to start Reverb service on production
# Usage: bash deployment/start-reverb.sh

set -e

echo "=========================================="
echo "Starting Laravel Reverb Service"
echo "=========================================="

# Load credentials
if [ ! -f .deploy-credentials ]; then
    echo "Error: .deploy-credentials file not found!"
    exit 1
fi

source .deploy-credentials

echo ""
echo "Target Server: $DROPLET_IP"
echo "User: $DROPLET_USER"
echo ""

echo "Connecting to server and starting Reverb..."
ssh -o StrictHostKeyChecking=no $DROPLET_USER@$DROPLET_IP << ENDSSH
echo "Starting Reverb service..."
echo '$DROPLET_SUDO_PASSWORD' | sudo -S systemctl start reverb

echo ""
echo "Checking service status..."
echo '$DROPLET_SUDO_PASSWORD' | sudo -S systemctl status reverb --no-pager -l

echo ""
echo "=========================================="
echo "Reverb Service Started!"
echo "=========================================="
echo ""
echo "WebSocket endpoint: wss://mygrownet.com/app"
echo ""
echo "Useful commands:"
echo "  View logs: sudo journalctl -u reverb -f"
echo "  Restart: sudo systemctl restart reverb"
echo "  Stop: sudo systemctl stop reverb"
echo "  Status: sudo systemctl status reverb"
echo ""
ENDSSH

echo ""
echo "Done! Reverb is now running."
echo ""
