# GrowMart Subdomain Setup Script (PowerShell)
# Sets up growmart.mygrownet.com subdomain on DigitalOcean droplet

$ErrorActionPreference = "Stop"

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "GrowMart Subdomain Setup" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Check if running from correct directory
if (-not (Test-Path ".deploy-credentials")) {
    Write-Host "Error: .deploy-credentials file not found!" -ForegroundColor Red
    Write-Host "Please ensure you're in the project root directory" -ForegroundColor Yellow
    Write-Host "and run setup-credentials.ps1 first" -ForegroundColor Yellow
    exit 1
}

# Load credentials
$credentials = @{}
Get-Content ".deploy-credentials" | ForEach-Object {
    if ($_ -match '^([^=]+)="([^"]*)"') {
        $credentials[$matches[1]] = $matches[2]
    }
}

$DROPLET_IP = $credentials['DROPLET_IP']
$DROPLET_USER = $credentials['DROPLET_USER']
$DROPLET_SUDO_PASSWORD = $credentials['DROPLET_SUDO_PASSWORD']

Write-Host "Target Server: $DROPLET_IP" -ForegroundColor Green
Write-Host "Subdomain: growmart.mygrownet.com" -ForegroundColor Green
Write-Host ""

# Check if scp and ssh are available
try {
    $null = Get-Command ssh -ErrorAction Stop
    $null = Get-Command scp -ErrorAction Stop
} catch {
    Write-Host "Error: SSH/SCP not found!" -ForegroundColor Red
    Write-Host "Please install OpenSSH or use Git Bash" -ForegroundColor Yellow
    Write-Host "Run: Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0" -ForegroundColor Yellow
    exit 1
}

# Step 1: Upload nginx configuration
Write-Host "Step 1: Uploading nginx configuration..." -ForegroundColor Yellow
try {
    scp "deployment/growmart-subdomain.conf" "${DROPLET_USER}@${DROPLET_IP}:/tmp/growmart-subdomain.conf"
    Write-Host "✓ Configuration uploaded" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to upload configuration" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 2: Configure nginx on server
Write-Host "Step 2: Configuring nginx..." -ForegroundColor Yellow
$sshCommands = @"
echo '$DROPLET_SUDO_PASSWORD' | sudo -S mv /tmp/growmart-subdomain.conf /etc/nginx/sites-available/growmart-subdomain.conf
if [ ! -f /etc/nginx/sites-enabled/growmart-subdomain.conf ]; then
    echo '$DROPLET_SUDO_PASSWORD' | sudo -S ln -s /etc/nginx/sites-available/growmart-subdomain.conf /etc/nginx/sites-enabled/
    echo '✓ Symlink created'
else
    echo '✓ Symlink already exists'
fi
echo '$DROPLET_SUDO_PASSWORD' | sudo -S nginx -t
"@

try {
    $result = ssh "${DROPLET_USER}@${DROPLET_IP}" $sshCommands
    Write-Host $result
    Write-Host "✓ Nginx configured successfully" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to configure nginx" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 3: Reload nginx
Write-Host "Step 3: Reloading nginx..." -ForegroundColor Yellow
try {
    ssh "${DROPLET_USER}@${DROPLET_IP}" "echo '$DROPLET_SUDO_PASSWORD' | sudo -S systemctl reload nginx"
    Write-Host "✓ Nginx reloaded" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to reload nginx" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 4: Update Laravel configuration
Write-Host "Step 4: Updating Laravel configuration..." -ForegroundColor Yellow
$laravelCommands = @"
cd /var/www/mygrownet.com
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
echo '✓ Laravel caches cleared and rebuilt'
"@

try {
    $result = ssh "${DROPLET_USER}@${DROPLET_IP}" $laravelCommands
    Write-Host $result
    Write-Host "✓ Laravel configuration updated" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to update Laravel configuration" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 5: Test subdomain
Write-Host "Step 5: Testing subdomain..." -ForegroundColor Yellow
Write-Host ""

Write-Host "Testing HTTP redirect..." -ForegroundColor Cyan
try {
    $httpResponse = Invoke-WebRequest -Uri "http://growmart.mygrownet.com" -MaximumRedirection 0 -ErrorAction SilentlyContinue
    if ($httpResponse.StatusCode -eq 301 -or $httpResponse.StatusCode -eq 302) {
        Write-Host "✓ HTTP redirects to HTTPS" -ForegroundColor Green
    } else {
        Write-Host "⚠ HTTP status: $($httpResponse.StatusCode) (expected 301 or 302)" -ForegroundColor Yellow
    }
} catch {
    if ($_.Exception.Response.StatusCode -eq 301 -or $_.Exception.Response.StatusCode -eq 302) {
        Write-Host "✓ HTTP redirects to HTTPS" -ForegroundColor Green
    } else {
        Write-Host "⚠ Could not test HTTP redirect" -ForegroundColor Yellow
    }
}
Write-Host ""

Write-Host "Testing HTTPS..." -ForegroundColor Cyan
try {
    $httpsResponse = Invoke-WebRequest -Uri "https://growmart.mygrownet.com" -ErrorAction Stop
    if ($httpsResponse.StatusCode -eq 200) {
        Write-Host "✓ HTTPS is working" -ForegroundColor Green
    } else {
        Write-Host "⚠ HTTPS status: $($httpsResponse.StatusCode) (expected 200)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ Could not test HTTPS: $($_.Exception.Message)" -ForegroundColor Yellow
}
Write-Host ""

# Summary
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "GrowMart subdomain is now configured at:" -ForegroundColor Green
Write-Host "  https://growmart.mygrownet.com" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Add DNS record in Cloudflare:" -ForegroundColor White
Write-Host "     Type: CNAME" -ForegroundColor Gray
Write-Host "     Name: growmart" -ForegroundColor Gray
Write-Host "     Target: mygrownet.com" -ForegroundColor Gray
Write-Host "     Proxy: Enabled (orange cloud)" -ForegroundColor Gray
Write-Host ""
Write-Host "  2. Wait 5-30 minutes for DNS propagation" -ForegroundColor White
Write-Host ""
Write-Host "  3. Test the subdomain in your browser:" -ForegroundColor White
Write-Host "     https://growmart.mygrownet.com" -ForegroundColor Gray
Write-Host ""
Write-Host "  4. Access admin panel:" -ForegroundColor White
Write-Host "     https://growmart.mygrownet.com/admin" -ForegroundColor Gray
Write-Host ""
Write-Host "Configuration files:" -ForegroundColor Yellow
Write-Host "  - Nginx: /etc/nginx/sites-available/growmart-subdomain.conf" -ForegroundColor Gray
Write-Host "  - Logs: /var/log/nginx/growmart-*.log" -ForegroundColor Gray
Write-Host ""
Write-Host "To view logs:" -ForegroundColor Yellow
Write-Host "  ssh ${DROPLET_USER}@${DROPLET_IP}" -ForegroundColor Gray
Write-Host "  sudo tail -f /var/log/nginx/growmart-error.log" -ForegroundColor Gray
Write-Host "  sudo tail -f /var/log/nginx/growmart-access.log" -ForegroundColor Gray
Write-Host ""
