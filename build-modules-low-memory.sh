#!/bin/bash

# MyGrowNet Modular Build Script
# Builds each module separately to avoid memory issues

set -e  # Exit on error

# Color output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Modules to build (module:memory_in_mb)
MODULES=(
    "main:768"
    "admin:512"
    "employee:512"
    "lifephus:512"
    "stockflow:512"
    "bizboost:512"
    "bizdocs:512"
    "grownet:512"
    "growbuilder:512"
    "growmart:512"
    "growstream:512"
    "growmusic:512"
    "zamstay:512"
    "bms:512"
    "primeedge:512"
    "venture:512"
    "growfinance:512"
    "marketplace:512"
)

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   MyGrowNet Modular Build System      ║${NC}"
echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo ""

TOTAL=${#MODULES[@]}
CURRENT=0
FAILED=()

START_TIME=$(date +%s)

for module_config in "${MODULES[@]}"; do
    IFS=':' read -r MODULE MEMORY <<< "$module_config"
    CURRENT=$((CURRENT + 1))
    
    echo -e "${BLUE}[${CURRENT}/${TOTAL}]${NC} Building ${GREEN}${MODULE}${NC} module (${MEMORY}MB)..."
    MODULE_START=$(date +%s)
    
    if NODE_OPTIONS="--max-old-space-size=${MEMORY}" MODULE="${MODULE}" npx vite build 2>&1 | tail -5; then
        MODULE_END=$(date +%s)
        MODULE_TIME=$((MODULE_END - MODULE_START))
        echo -e "${GREEN}✓${NC} ${MODULE} built successfully (${MODULE_TIME}s)"
        echo ""
    else
        MODULE_END=$(date +%s)
        MODULE_TIME=$((MODULE_END - MODULE_START))
        echo -e "${RED}✗${NC} ${MODULE} build failed (${MODULE_TIME}s)"
        FAILED+=("$MODULE")
        echo ""
    fi
done

END_TIME=$(date +%s)
TOTAL_TIME=$((END_TIME - START_TIME))
MINUTES=$((TOTAL_TIME / 60))
SECONDS=$((TOTAL_TIME % 60))

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║         Build Summary                  ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "Total time: ${MINUTES}m ${SECONDS}s"
echo ""

if [ ${#FAILED[@]} -eq 0 ]; then
    echo -e "${GREEN}✓ All ${TOTAL} modules built successfully!${NC}"
    echo ""
    echo "Built modules:"
    for module_config in "${MODULES[@]}"; do
        IFS=':' read -r MODULE MEMORY <<< "$module_config"
        echo -e "  ${GREEN}•${NC} ${MODULE}"
    done
    exit 0
else
    echo -e "${RED}✗ ${#FAILED[@]} module(s) failed:${NC}"
    for failed_module in "${FAILED[@]}"; do
        echo -e "  ${RED}•${NC} ${failed_module}"
    done
    echo ""
    echo -e "${GREEN}Successful modules:${NC}"
    for module_config in "${MODULES[@]}"; do
        IFS=':' read -r MODULE MEMORY <<< "$module_config"
        if [[ ! " ${FAILED[@]} " =~ " ${MODULE} " ]]; then
            echo -e "  ${GREEN}•${NC} ${MODULE}"
        fi
    done
    exit 1
fi
