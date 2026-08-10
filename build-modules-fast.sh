#!/bin/bash

# MyGrowNet FAST Low-Memory Parallel Build Script
# Optimized for low-RAM systems (uses 2 parallel jobs, 512MB limit per process, 1024MB for main)

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
    "growstream"
    "growmusic"
    "zamstay"
    "bms"
    "primeedge"
    "venture"
    "growfinance"
    "marketplace"
)

# Configurable batch size (defaults to 2 for low RAM) and max memory (defaults to 512MB)
BATCH_SIZE=${BATCH_SIZE:-2}
MAX_MEMORY=${MAX_MEMORY:-512}

echo -e "${YELLOW}╔════════════════════════════════════════╗${NC}"
echo -e "${YELLOW}║   FAST PARALLEL BUILD (${BATCH_SIZE} at a time, ${MAX_MEMORY}MB limit) ║${NC}"
echo -e "${YELLOW}╚════════════════════════════════════════╝${NC}"
echo ""

TOTAL=${#MODULES[@]}
FAILED=()

build_module() {
    local MODULE=$1
    local MEMORY=$MAX_MEMORY
    [[ "$MODULE" == "main" ]] && MEMORY=1024
    
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
    declare -A MODULE_MAP
    for MODULE in "${BATCH[@]}"; do
        build_module "$MODULE" &
        PID=$!
        PIDS+=($PID)
        MODULE_MAP[$PID]=$MODULE
    done
    
    # Wait for batch processes to complete
    for PID in "${PIDS[@]}"; do
        wait $PID
        EXIT_CODE=$?
        if [ $EXIT_CODE -ne 0 ]; then
            FAILED+=("${MODULE_MAP[$PID]:-$PID}")
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
    echo -e "${RED}✗ ${#FAILED[@]} module(s) failed:${NC}"
    for m in "${FAILED[@]}"; do
        echo -e "  ${RED}•${NC} $m"
    done
    exit 1
fi
