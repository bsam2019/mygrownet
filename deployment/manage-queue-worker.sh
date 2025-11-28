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

ACTION=${1:-status}

echo "🔧 Queue Worker Management - Action: $ACTION"
echo "📍 Server: $DROPLET_IP"

case $ACTION in
    status)
        ssh ${DROPLET_USER}@${DROPLET_IP} "echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl status mygrownet-worker:*"
        ;;
    start)
        ssh ${DROPLET_USER}@${DROPLET_IP} "echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl start mygrownet-worker:*"
        ;;
    stop)
        ssh ${DROPLET_USER}@${DROPLET_IP} "echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl stop mygrownet-worker:*"
        ;;
    restart)
        ssh ${DROPLET_USER}@${DROPLET_IP} "echo '${DROPLET_SUDO_PASSWORD}' | sudo -S supervisorctl restart mygrownet-worker:*"
        ;;
    logs)
        ssh ${DROPLET_USER}@${DROPLET_IP} "tail -100 ${PROJECT_PATH}/storage/logs/worker.log"
        ;;
    *)
        echo "Usage: $0 {status|start|stop|restart|logs}"
        exit 1
        ;;
esac
