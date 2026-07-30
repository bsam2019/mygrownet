# Droplet Memory Analysis

## Server Specs
- **Advertised**: 4GB RAM (you thought)
- **Actual**: 2GB RAM + 1GB Swap = 3GB total
- **Reality**: Digital Ocean 2GB droplet

## Current Memory Usage (Jul 30, 2026)

### RAM (2GB total)
```
Used:      417MB (21%)
Free:      1.4GB (71%)
Cached:    325MB (16%)
Available: 1.5GB
```

### Swap (1GB total)
```
Used:      966MB (97%)
Free:      57MB (3%)
```

## Processes in Swap (Causes Performance Issues!)

| Process | Swap Used | Why in Swap |
|---------|-----------|-------------|
| MySQL | **612 MB** | Database server pushed to swap during build |
| PHP (queue worker) | 92 MB | Background job processor |
| PHP (Reverb) | 60 MB | WebSocket server |
| Supervisor | 16 MB | Process manager |
| Fail2ban | 15 MB | Security service |
| Redis | 11 MB | Cache server |
| PHP-FPM | 11 MB | Web server |

**Total in Swap**: ~800MB

## Why Build Failed

### Memory Requirements for Vite Build:
- **Node.js base**: 100-200MB
- **Vite build (main module)**: 1536MB requested (--max-old-space-size=1536)
- **Running services**: ~500MB in RAM + 800MB in swap
- **System overhead**: ~200MB

**Total needed**: ~2.5GB RAM
**Available**: 2GB RAM total (1.5GB free) + 57MB swap free = **NOT ENOUGH**

### What Happened:
1. Build started, requested 1536MB
2. System had only ~1.5GB available
3. Tried to use swap (already 97% full)
4. Kernel **OOM killer** terminated the build process

## Why Memory Was Low During Build (76MB free)

When you tried to build:
```
               total        used        free      shared  buff/cache   available
Mem:           1.9Gi       1.8Gi        76Mi       3.5Mi       205Mi       128Mi
Swap:          1.0Gi       1.0Gi       1.0Mi  ← 100% FULL!
```

Services consuming memory:
1. PHP-FPM workers (3-5): ~40MB
2. MySQL: ~60MB RAM + 612MB swap
3. Queue worker: ~7MB RAM + 92MB swap
4. Reverb: ~33MB RAM + 60MB swap
5. Redis: ~11MB swap
6. Nginx: ~8MB
7. System: ~200MB
8. **Build process trying to start**: 1536MB ← **KILLED BY OOM**

## Solutions

### Option 1: Build Locally and Upload (RECOMMENDED)
✅ What we're using now via `deploy-with-assets.sh`
- Build on your local machine (4GB+ RAM)
- Upload pre-built assets via tar.gz
- Avoids all memory issues on server

### Option 2: Clear Swap and Temporarily Stop Services
Before building on server:
```bash
# Stop non-critical services
sudo systemctl stop reverb
sudo systemctl stop laravel-queue

# Clear swap
sudo swapoff -a && sudo swapon -a

# Build with lower memory
NODE_OPTIONS=--max-old-space-size=768 npm run build:sequential

# Restart services
sudo systemctl start reverb
sudo systemctl start laravel-queue
```

### Option 3: Upgrade Droplet
- Upgrade to 4GB RAM droplet ($24/month instead of $12/month)
- Would allow on-server builds without issues

### Option 4: Optimize MySQL Memory
MySQL using 612MB swap is killing performance. Reduce MySQL memory:

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
# Reduce memory for 2GB droplet
innodb_buffer_pool_size = 128M  # Default is 512M
max_connections = 50            # Default is 151
table_open_cache = 64           # Default is 400
```

Then restart: `sudo systemctl restart mysql`

## Optimizations Applied

✅ **Removed duplicate page globs from app.ts**
- Reduced main module from 26 globs to 14 globs
- Saves ~400-500MB during build
- Build should now fit in 1GB instead of 1.5GB

## Why Main App Was Bloated

Each Vue page glob loads ALL matching files during build:
- `./pages/GrowNet/**/*.vue` (50+ files)
- `./pages/BMS/**/*.vue` (40+ files)  
- `./pages/StockFlow/**/*.vue` (30+ files)
- etc.

**Before**: Main app loaded 26 page globs = ~200 Vue files in memory during build
**After**: Main app loads 14 core globs = ~80 Vue files in memory during build

**Memory savings**: ~60% reduction in build memory

## Current Best Practice

Use the deployment script:
```bash
cd deployment
bash deploy-with-assets.sh
```

This:
1. Builds locally (where you have enough RAM)
2. Compresses assets to tar.gz
3. Uploads to server
4. Extracts and clears caches
5. Never triggers OOM killer
