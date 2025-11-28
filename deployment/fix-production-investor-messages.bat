@echo off
REM Fix Production Investor Messages Status Column Issue
REM This script ensures the investor_messages table has the required status column

echo === Production Fix: Investor Messages Status Column ===
echo Date: %date% %time%
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ Error: Not in Laravel root directory
    exit /b 1
)

echo 📋 Step 1: Checking current migration status...
php artisan migrate:status | findstr "investor_messages"

echo.
echo 📋 Step 2: Running migrations to ensure table is up to date...
php artisan migrate --force

echo.
echo 📋 Step 3: Running custom fix script...
php fix-investor-messages-status-column.php

echo.
echo 📋 Step 4: Clearing application caches...
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo.
echo 📋 Step 5: Testing the fix...
echo Testing problematic query...

REM Test the specific query that was failing
php -r "require_once 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); try { $count = DB::table('investor_messages')->where('investor_account_id', 1)->where('direction', 'to_investor')->where('status', 'unread')->count(); echo \"✅ Query successful. Unread messages: $count\n\"; } catch (Exception $e) { echo \"❌ Query failed: \" . $e->getMessage() . \"\n\"; exit(1); }"

echo.
echo ✅ Production fix completed successfully!
echo The investor portal should now work without the status column error.
pause