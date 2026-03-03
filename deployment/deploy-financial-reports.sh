#!/bin/bash

# Financial Reports Deployment Script
# Deploys the transaction-based financial reporting system

set -e

echo "========================================="
echo "Financial Reports Deployment"
echo "========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Please run this script from the project root.${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Installing Chart.js dependency...${NC}"
npm install chart.js
echo -e "${GREEN}✓ Chart.js installed${NC}"
echo ""

echo -e "${YELLOW}Step 2: Building frontend assets...${NC}"
npm run build
echo -e "${GREEN}✓ Assets built${NC}"
echo ""

echo -e "${YELLOW}Step 3: Clearing caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 4: Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Application optimized${NC}"
echo ""

echo -e "${YELLOW}Step 5: Verifying routes...${NC}"
ROUTE_COUNT=$(php artisan route:list | grep -c "financial/v2" || true)
if [ "$ROUTE_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ Found $ROUTE_COUNT financial/v2 routes${NC}"
else
    echo -e "${RED}✗ No financial/v2 routes found!${NC}"
    echo "Please check routes/admin.php"
    exit 1
fi
echo ""

echo -e "${YELLOW}Step 6: Testing API endpoint...${NC}"
# Test if the application is running
if curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/financial/v2/overview?period=month | grep -q "200\|302"; then
    echo -e "${GREEN}✓ API endpoint responding${NC}"
else
    echo -e "${YELLOW}⚠ Could not test API endpoint (app may not be running locally)${NC}"
fi
echo ""

echo "========================================="
echo -e "${GREEN}Deployment Complete!${NC}"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Login as admin"
echo "2. Navigate to Finance → Financial Reports"
echo "3. Verify data loads correctly"
echo ""
echo "Dashboard URL: /admin/financial/v2/dashboard"
echo ""
echo "Troubleshooting:"
echo "- If charts don't show: npm run build"
echo "- If routes 404: php artisan route:clear && php artisan route:cache"
echo "- If no data: Check transactions table has data"
echo ""
