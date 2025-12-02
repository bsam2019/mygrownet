# Reset User Session on Production
# Usage: .\deployment\reset-user-session.ps1 -UserId 123

param(
    [Parameter(Mandatory=$true)]
    [int]$UserId
)

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$CredentialsFile = Join-Path $ProjectRoot ".deploy-credentials"

# Load credentials
if (Test-Path $CredentialsFile) {
    $credentials = @{}
    Get-Content $CredentialsFile | ForEach-Object {
        if ($_ -match '^([^#][^=]+)="?([^"]*)"?$') {
            $credentials[$matches[1].Trim()] = $matches[2].Trim()
        }
    }
    
    $DROPLET_IP = $credentials["DROPLET_IP"]
    $DROPLET_USER = $credentials["DROPLET_USER"]
    $PROJECT_PATH = $credentials["PROJECT_PATH"]
} else {
    Write-Host "❌ Error: .deploy-credentials file not found!" -ForegroundColor Red
    Write-Host "Please create .deploy-credentials file in project root."
    Write-Host "Copy from .deploy-credentials.example and fill in your credentials."
    exit 1
}

# Validate credentials loaded
if (-not $DROPLET_IP -or -not $DROPLET_USER -or -not $PROJECT_PATH) {
    Write-Host "❌ Error: Missing credentials in .deploy-credentials file!" -ForegroundColor Red
    Write-Host "Required: DROPLET_IP, DROPLET_USER, PROJECT_PATH"
    exit 1
}

Write-Host "🔄 Resetting user session on production..." -ForegroundColor Cyan
Write-Host "📍 Server: $DROPLET_IP" -ForegroundColor Gray
Write-Host "👤 User ID: $UserId" -ForegroundColor Gray
Write-Host ""

# SSH and run the session reset directly via tinker (no custom command needed)
$tinkerScript = @"
\`$user = App\Models\User::find($UserId);
if (!\`$user) { echo 'ERROR: User not found!'; exit(1); }
echo 'User: ' . \`$user->name . ' (' . \`$user->email . ')' . PHP_EOL;
\`$user->update(['remember_token' => null]);
echo '✅ Remember token cleared' . PHP_EOL;
\`$sessions = DB::table('sessions')->where('user_id', $UserId)->delete();
echo '✅ Deleted ' . \`$sessions . ' session(s)' . PHP_EOL;
if (Schema::hasTable('personal_access_tokens')) {
    \`$tokens = DB::table('personal_access_tokens')->where('tokenable_type', 'App\Models\User')->where('tokenable_id', $UserId)->delete();
    echo '✅ Deleted ' . \`$tokens . ' access token(s)' . PHP_EOL;
}
echo 'Session reset complete!';
"@

$sshCommand = "cd $PROJECT_PATH && php artisan tinker --execute=`"$tinkerScript`""
ssh "${DROPLET_USER}@${DROPLET_IP}" $sshCommand

Write-Host ""
Write-Host "🎉 Done! User $UserId will need to log in again on production." -ForegroundColor Green
