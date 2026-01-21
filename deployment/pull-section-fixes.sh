#!/bin/bash

# Pull GrowBuilder Section Fixes to Production
# Fixes: Page header alignment and font size controls

set -e

echo "🚀 Pulling GrowBuilder section fixes to production..."

# Load credentials
source .deploy-credentials

# SSH into droplet and pull changes
ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
    cd /var/www/mygrownet.com
    
    echo "📥 Pulling latest changes from GitHub..."
    git pull origin main
    
    echo "✅ Changes pulled successfully!"
    
    echo ""
    echo "📝 Changes included:"
    echo "  - Fixed page header vertical alignment"
    echo "  - Implemented font size controls for page headers"
    echo "  - Both editor and live site now respect custom font sizes"
    echo ""
    echo "🎉 Deployment complete!"
ENDSSH

echo ""
echo "✅ All done! The fixes are now live on production."
echo ""
echo "Test the fixes at:"
echo "  - https://chisambofarms.mygrownet.com (live site)"
echo "  - https://mygrownet.com/growbuilder/sites (editor)"
