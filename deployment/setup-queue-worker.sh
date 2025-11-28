#!/bin/bash

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

echo "🔧 Setting up Laravel Queue Worker on droplet..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

echo "📦 Installing Supervisor (if not installed)..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S apt-get update -qq
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S apt-get install -y supervisor

echo "📝 Creating Supervisor configuration..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S tee /etc/supervisor/conf.d/mygrownet-worker.conf > /dev/null << 'EOF'
[program:mygrownet-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${PROJECT_PATH}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=${PROJECT_PATH}/storage/logs/worker.log
stopwaitsecs=3600
EOF

echo "🔄 Reloading Supervisor..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl reread
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl update
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl start mygrownet-worker:*

echo "✅ Checking status..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl status

echo "✅ Queue worker setup complete!"

ENDSSH

echo "🎉 Done! Queue worker is now running on your droplet."
