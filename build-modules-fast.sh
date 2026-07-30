#!/bin/bash

# MyGrowNet FAST Parallel Build Script
# Builds 4 modules at a time for speed (requires ~4-6GB RAM)

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Modules to build
MODULES=(
    "main"
    "admin"
    "employee"
    "lifephus"
    "stockflow"
    "bizboost"
    "bizdocs"
    "grownet"
    "growbuilder"
    "growmart"
    "zamstay"
    "bms"
    "primeedge"
    "venture"
    "growfinance"
    "marketplace"
)

echo -e "${YELLOW}╔════════════════════════════════════════╗${NC}"
echo -e "${YELLOW}║   FAST PARALLEL BUILD (4 at a time)    ║${NC}"
echo -e "${YELLOW}╚════════════════════════════════════════╝${NC}"
echo ""

TOTAL=${#MODULES[@]}
FAILED=()
BATCH_SIZE=4

build_module() {
    local MODULE=$1
    local MEMORY=1024
    [[ "$MODULE" == "main" ]] && MEMORY=1536
    
    if NODE_OPTIONS="--max-old-space-size=${MEMORY}" MODULE="${MODULE}" npx vite build 2>&1 | sed "s/^/[$MODULE] /"; then
        echo -e "${GREEN}✓${NC} ${MODULE} completed"
        return 0
    else
        echo -e "${RED}✗${NC} ${MODULE} failed"
        return 1
    fi
}

# Build in batches
for ((i=0; i<${#MODULES[@]}; i+=BATCH_SIZE)); do
    BATCH=("${MODULES[@]:i:BATCH_SIZE}")
    BATCH_NUM=$((i/BATCH_SIZE + 1))
    TOTAL_BATCHES=$(( (TOTAL + BATCH_SIZE - 1) / BATCH_SIZE ))
    
    echo -e "${BLUE}Batch ${BATCH_NUM}/${TOTAL_BATCHES}:${NC} ${BATCH[*]}"
    
    PIDS=()
    for MODULE in "${BATCH[@]}"; do
        build_module "$MODULE" &
        PIDS+=($!)
    done
    
    # Wait for batch to complete
    for PID in "${PIDS[@]}"; do
        if ! wait $PID; then
            FAILED+=("module")
        fi
    done
    
    echo ""
done

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║         Build Summary                  ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

if [ ${#FAILED[@]} -eq 0 ]; then
    echo -e "${GREEN}✓ All ${TOTAL} modules built successfully!${NC}"
    exit 0
else
    echo -e "${RED}✗ ${#FAILED[@]} module(s) failed${NC}"
    exit 1
fi
