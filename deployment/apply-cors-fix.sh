#!/bin/bash

# Apply CORS fix for GrowBuilder subdomains
# Usage: bash deployment/apply-cors-fix.sh

set -e

SERVER="sammy@138.197.187.134"
PROJECT_PATH="/var/www/mygrownet.com"

echo "🔧 Applying CORS fix to production server..."

# Upload the fix script
echo "📤 Uploading CORS fix script..."
scp deployment/fix-subdomain-cors.sh $SERVER:$PROJECT_PATH/deployment/

# Execute the fix script on server
echo "🚀 Executing CORS fix on server..."
ssh -t $SERVER "cd $PROJECT_PATH && printf 'Bsam@2025!!\n' | sudo -S bash deployment/fix-subdomain-cors.sh"

echo ""
echo "✅ CORS fix applied!"
echo "🧪 Test by visiting: https://ndelimas.mygrownet.com"
echo ""
