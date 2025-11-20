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

echo "📱 Checking Esaya's phone number..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

echo "=== User ID 11 (Esaya) ==="
php artisan tinker << 'PHP'
$user = \App\Models\User::find(11);
if ($user) {
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Phone: " . ($user->phone ?? "NULL") . "\n";
    echo "Profile Phone: " . ($user->profile?->phone_number ?? "NULL") . "\n";
} else {
    echo "User not found\n";
}
PHP

ENDSSH

echo "✅ Done!"
