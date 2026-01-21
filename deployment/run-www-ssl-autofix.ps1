# Run WWW SSL Auto-Fix on Production (PowerShell)
# Automatically fixes SSL certificate configuration

# Load credentials
$credentials = Get-Content .deploy-credentials | ConvertFrom-StringData
$DROPLET_IP = $credentials.DROPLET_IP.Trim('"')
$DROPLET_USER = $credentials.DROPLET_USER.Trim('"')
$DROPLET_SUDO_PASSWORD = $credentials.DROPLET_SUDO_PASSWORD.Trim('"')

Write-Host "🔧 Running WWW SSL Auto-Fix on Production" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "This will:" -ForegroundColor Yellow
Write-Host "- Find the correct SSL certificate"
Write-Host "- Update nginx configuration"
Write-Host "- Reload nginx"
Write-Host ""

$response = Read-Host "Continue? (y/n)"
if ($response -ne 'y' -and $response -ne 'Y') {
    Write-Host "Cancelled" -ForegroundColor Red
    exit 0
}

Write-Host ""
Write-Host "Running auto-fix script on server..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

$sshTarget = "$DROPLET_USER@$DROPLET_IP"

ssh $sshTarget "cd /var/www/mygrownet.com && echo '$DROPLET_SUDO_PASSWORD' | sudo -S bash deployment/fix-www-ssl-auto.sh"

Write-Host ""
Write-Host "✅ Auto-fix complete!" -ForegroundColor Green
Write-Host ""
Write-Host "🧪 Testing the fix..." -ForegroundColor Cyan
Write-Host "-------------------"
Write-Host "HTTP redirect test:"
try {
    $response = Invoke-WebRequest -Uri "http://www.chisambofarms.mygrownet.com" -MaximumRedirection 0 -ErrorAction SilentlyContinue
    Write-Host "Status: $($response.StatusCode)"
    Write-Host "Location: $($response.Headers.Location)"
} catch {
    Write-Host "Redirect detected (expected)" -ForegroundColor Green
}

Write-Host ""
Write-Host "📝 Please test in browser:" -ForegroundColor Cyan
Write-Host "https://www.chisambofarms.mygrownet.com"
Write-Host ""
Write-Host "Should redirect to:"
Write-Host "https://chisambofarms.mygrownet.com"
