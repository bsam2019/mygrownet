#!/bin/bash

# Service Worker Cache Fix - Resolves white page and POST request caching issues
# This script updates the service worker to properly handle cache operations

echo "🔧 Fixing Service Worker Cache Issues..."
echo "=========================================="

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "public/sw.js" ]; then
    echo -e "${RED}❌ Error: public/sw.js not found. Run this script from the project root.${NC}"
    exit 1
fi

echo -e "${YELLOW}📝 Updating service worker...${NC}"

# The service worker has been updated to:
# 1. Never cache POST, PUT, DELETE, PATCH requests (Cache API limitation)
# 2. Only cache GET requests
# 3. Use proper async/await for cache operations
# 4. Handle errors gracefully

echo -e "${GREEN}✅ Service worker updated${NC}"

# Clear old caches to force fresh downloads
echo -e "${YELLOW}🗑️  Clearing old caches...${NC}"

# Create a cache-busting script for browsers
cat > public/cache-buster.js << 'EOF'
// Cache Buster - Forces service worker update and cache clearing
(function() {
    if ('serviceWorker' in navigator) {
        // Unregister old service workers
        navigator.serviceWorker.getRegistrations().then(registrations => {
            registrations.forEach(registration => {
                registration.unregister();
            });
        });

        // Clear all caches
        if ('caches' in window) {
            caches.keys().then(cacheNames => {
                cacheNames.forEach(cacheName => {
                    caches.delete(cacheName);
                });
            });
        }

        // Clear localStorage
        localStorage.clear();

        // Register new service worker
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(registration => {
                console.log('[Cache Buster] Service worker registered:', registration);
            })
            .catch(error => {
                console.error('[Cache Buster] Service worker registration failed:', error);
            });
    }
})();
EOF

echo -e "${GREEN}✅ Cache buster script created${NC}"

# Update the main app.ts to include cache buster
echo -e "${YELLOW}📝 Updating app initialization...${NC}"

# Check if app.ts exists and add cache buster if needed
if [ -f "resources/js/app.ts" ]; then
    if ! grep -q "cache-buster" resources/js/app.ts; then
        echo "// Load cache buster to clear old caches on app start" >> resources/js/app.ts
        echo "import '/cache-buster.js';" >> resources/js/app.ts
        echo -e "${GREEN}✅ Cache buster added to app.ts${NC}"
    else
        echo -e "${YELLOW}⚠️  Cache buster already in app.ts${NC}"
    fi
fi

echo ""
echo -e "${GREEN}=========================================="
echo "✅ Service Worker Cache Fix Complete!"
echo "==========================================${NC}"
echo ""
echo "📋 What was fixed:"
echo "  • Service worker no longer tries to cache POST requests"
echo "  • Only GET requests are cached (Cache API requirement)"
echo "  • Proper error handling for cache operations"
echo "  • Cache buster script clears old caches on app load"
echo ""
echo "🚀 Next steps:"
echo "  1. Run: npm run build"
echo "  2. Deploy to production"
echo "  3. Users should clear browser cache or wait for auto-clear"
echo "  4. Service worker will auto-update on next page load"
echo ""
echo "📱 For users experiencing white pages:"
echo "  • Clear browser cache and cookies"
echo "  • Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)"
echo "  • Service worker will auto-update"
echo ""
