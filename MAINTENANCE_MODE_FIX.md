# Maintenance Mode Auto-Disable Issue - SOLVED

## The Problem

Your site automatically exits maintenance mode after a few minutes because **GitHub Actions auto-deploys on every push to main**.

### What Happens:
1. You run: `php artisan down` (maintenance mode enabled)
2. You push code to fix something: `git push origin main`
3. **GitHub Actions automatically triggers** (workflow runs on push)
4. Deployment script runs: `php artisan optimize:clear`
5. **The `down` file gets removed!**
6. Site comes back online automatically

## The Root Cause

`.github/workflows/deploy.yml` is configured to run on **every push to main**:

```yaml
on:
  push:
    branches: [main]  # ← THIS causes auto-deploy
  workflow_dispatch:
```

The deployment runs `php artisan optimize:clear` which clears the `storage/framework/down` file.

## Solutions

### Option 1: Disable Auto-Deploy (Quick Fix)

Edit `.github/workflows/deploy.yml` on GitHub:

1. Go to: https://github.com/bsam2019/mygrownet/blob/main/.github/workflows/deploy.yml
2. Click "Edit" (pencil icon)
3. Change lines 3-5 from:
```yaml
on:
  push:
    branches: [main]
  workflow_dispatch:
```

To:
```yaml
on:
  # Auto-deploy disabled to prevent maintenance mode issues
  # push:
  #   branches: [main]
  workflow_dispatch:  # Manual trigger only
```

4. Commit directly to main

Now deployments only run when you **manually trigger** them from GitHub Actions tab.

### Option 2: Preserve Maintenance Mode During Deployment (Better)

Add this logic to the deployment script in `.github/workflows/deploy.yml`:

**In the "Deploy to server" step** (around line 38), change:
```yaml
script: |
  cd /var/www/mygrownet.com
  git pull origin main
  php artisan optimize:clear
  php artisan optimize
```

To:
```yaml
script: |
  cd /var/www/mygrownet.com
  
  # Check if site is in maintenance mode
  MAINTENANCE_MODE=false
  if [ -f storage/framework/down ]; then
    MAINTENANCE_MODE=true
    echo "⚠️  Site is in maintenance mode - will restore after deployment"
    cp storage/framework/down /tmp/mygrownet-down-backup
  fi
  
  git pull origin main
  php artisan optimize:clear
  php artisan optimize
  
  # Restore maintenance mode if it was active
  if [ "$MAINTENANCE_MODE" = true ]; then
    mv /tmp/mygrownet-down-backup storage/framework/down
    echo "✅ Maintenance mode restored"
  fi
```

**In the "Fix permissions and finalize" step** (around line 56), add the same logic:
```yaml
script: |
  cd /var/www/mygrownet.com
  
  # Check if site was in maintenance mode
  MAINTENANCE_MODE=false
  if [ -f /tmp/mygrownet-down-backup ]; then
    MAINTENANCE_MODE=true
  fi
  
  mkdir -p public/build/.vite
  if [ -f public/build/manifest.json ]; then
    cp public/build/manifest.json public/build/.vite/manifest.json
  fi
  php artisan optimize:clear
  php artisan optimize
  
  # Restore maintenance mode if it was active
  if [ "$MAINTENANCE_MODE" = true ]; then
    mv /tmp/mygrownet-down-backup storage/framework/down
    echo "✅ Maintenance mode restored"
  fi
  
  echo "✅ Deployment complete!"
```

### Option 3: Use Maintenance Mode with Secret (Best for deployments)

When putting site in maintenance, use a secret bypass:

```bash
php artisan down --secret="deploy2026"
```

Then during deployment, the scripts can still access the site using:
```
https://mygrownet.com/deploy2026
```

This allows deployments to test the site while keeping it down for regular users.

## Recommended Approach

**For now**: Use **Option 1** (disable auto-deploy)
- Prevents maintenance mode from being cleared
- You manually trigger deployments when ready
- Gives you full control

**Long term**: Implement **Option 2** (preserve maintenance mode)
- Keeps auto-deploy feature
- Respects maintenance mode
- Best of both worlds

## Manual Deployment Process

With auto-deploy disabled, deploy manually:

1. Go to: https://github.com/bsam2019/mygrownet/actions
2. Click "Build and Deploy" workflow
3. Click "Run workflow" button
4. Select `main` branch
5. Click "Run workflow"

Or use local deployment scripts:
```bash
cd deployment
bash deploy-with-assets.sh
```

## Testing the Fix

1. Put site in maintenance: `php artisan down`
2. Push some code: `git push origin main`
3. Wait 5 minutes
4. Check site - **it should still be in maintenance mode!**
5. Manually take it out when ready: `php artisan up`

## Why This Wasn't Obvious

- GitHub Actions runs silently in the background
- No notification when it clears maintenance mode
- Logs are in GitHub, not on your server
- Looks like maintenance mode "expires" on its own

You can check GitHub Actions history:
https://github.com/bsam2019/mygrownet/actions

You'll see workflows that ran automatically after your pushes.
