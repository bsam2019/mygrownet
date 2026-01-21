# Diagnose WWW SSL Issue on Production (PowerShell)
# Pulls latest changes and runs diagnostic

# Load credentials
$credentials = Get-Content .deploy-credentials | ConvertFrom-StringData
$DROPLET_IP = $credentials.DROPLET_IP.Trim('"')
$DROPLET_USER = $credentials.DROPLET_USER.Trim('"')
$DROPLET_SUDO_PASSWORD = $credentials.DROPLET_SUDO_PASSWORD.Trim('"')

Write-Host "🔍 Diagnosing WWW SSL Issue on Production" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "1️⃣ Pulling latest changes..." -ForegroundColor Yellow
Write-Host "---------------------------"

$sshTarget = "$DROPLET_USER@$DROPLET_IP"

ssh $sshTarget 'cd /var/www/mygrownet.com && git pull origin main'

Write-Host ""
Write-Host "2️⃣ Running SSL diagnostic..." -ForegroundColor Yellow
Write-Host "---------------------------"

ssh $sshTarget "cd /var/www/mygrownet.com && echo '$DROPLET_SUDO_PASSWORD' | sudo -S bash deployment/check-ssl-certificates.sh"

Write-Host ""
Write-Host "✅ Diagnostic complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next steps based on diagnostic results:" -ForegroundColor Cyan
Write-Host "1. If certificate paths are wrong → Run: .\deployment\run-www-ssl-autofix.ps1"
Write-Host "2. If certificate doesn't exist → Run: .\deployment\run-generate-wildcard-ssl.ps1"
Write-Host "3. If nginx config has errors → Check the error messages above"
