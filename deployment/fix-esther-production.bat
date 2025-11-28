@echo off
REM Esther Ziwa Account Fix - Production Deployment Script (Windows)
REM Usage: deployment\fix-esther-production.bat

echo === ESTHER ZIWA ACCOUNT FIX - PRODUCTION DEPLOYMENT ===
echo Started at: %date% %time%
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ ERROR: Not in Laravel root directory
    echo Please run this script from the Laravel project root
    pause
    exit /b 1
)

REM Create logs directory if it doesn't exist
if not exist "storage\logs\account-fixes" mkdir "storage\logs\account-fixes"

REM Set log file with timestamp
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "TIMESTAMP=%dt:~0,4%%dt:~4,2%%dt:~6,2%_%dt:~8,2%%dt:~10,2%%dt:~12,2%"
set "LOG_DIR=storage\logs\account-fixes"
set "DIAGNOSIS_LOG=%LOG_DIR%\esther_diagnosis_%TIMESTAMP%.log"
set "FIX_LOG=%LOG_DIR%\esther_fix_%TIMESTAMP%.log"
set "VERIFICATION_LOG=%LOG_DIR%\esther_verification_%TIMESTAMP%.log"

echo 📁 Log files will be saved to:
echo    Diagnosis: %DIAGNOSIS_LOG%
echo    Fix: %FIX_LOG%
echo    Verification: %VERIFICATION_LOG%
echo.

REM Step 1: Database backup notice
echo 1. DATABASE BACKUP
echo ==================
echo ⚠️  Please ensure you have created a database backup before proceeding
echo.
set /p BACKUP_CONFIRM="Have you created a database backup? (yes/no): "
if /i not "%BACKUP_CONFIRM%"=="yes" (
    echo ❌ Please create a database backup first
    pause
    exit /b 1
)
echo.

REM Step 2: Run diagnosis
echo 2. RUNNING DIAGNOSIS
echo ====================

if exist "diagnose-esther-account.php" (
    echo Running diagnostic script...
    php diagnose-esther-account.php > "%DIAGNOSIS_LOG%" 2>&1
    echo ✅ Diagnosis complete - check %DIAGNOSIS_LOG%
    
    REM Show summary (last 10 lines)
    echo.
    echo Diagnosis Summary:
    echo ------------------
    powershell "Get-Content '%DIAGNOSIS_LOG%' | Select-Object -Last 10"
) else (
    echo ⚠️  diagnose-esther-account.php not found - skipping diagnosis
)

echo.

REM Step 3: Confirm before proceeding
echo 3. CONFIRMATION
echo ===============

echo ⚠️  IMPORTANT: About to apply fixes to Esther Ziwa's account
echo.
echo This will:
echo - Investigate her transaction history
echo - Fix negative balance issues
echo - Add corrective transactions if needed
echo - Clear application caches
echo.

set /p CONFIRM="Do you want to proceed with the fix? (yes/no): "
if /i not "%CONFIRM%"=="yes" (
    echo ❌ Fix cancelled by user
    pause
    exit /b 1
)

echo.

REM Step 4: Apply fix
echo 4. APPLYING FIX
echo ===============

if exist "fix-esther-ziwa-negative-balance.php" (
    echo Running fix script...
    php fix-esther-ziwa-negative-balance.php > "%FIX_LOG%" 2>&1
    
    REM Check if fix was successful
    if %errorlevel% equ 0 (
        echo ✅ Fix script completed - check %FIX_LOG%
        
        REM Show summary (last 15 lines)
        echo.
        echo Fix Summary:
        echo ------------
        powershell "Get-Content '%FIX_LOG%' | Select-Object -Last 15"
    ) else (
        echo ❌ Fix script failed - check %FIX_LOG% for details
        pause
        exit /b 1
    )
) else (
    echo ❌ fix-esther-ziwa-negative-balance.php not found
    pause
    exit /b 1
)

echo.

REM Step 5: Run verification
echo 5. VERIFICATION
echo ===============

if exist "diagnose-esther-account.php" (
    echo Running verification...
    php diagnose-esther-account.php > "%VERIFICATION_LOG%" 2>&1
    echo ✅ Verification complete - check %VERIFICATION_LOG%
    
    REM Show summary (last 10 lines)
    echo.
    echo Verification Summary:
    echo --------------------
    powershell "Get-Content '%VERIFICATION_LOG%' | Select-Object -Last 10"
) else (
    echo ⚠️  Skipping verification - diagnostic script not found
)

echo.

REM Step 6: Clear caches
echo 6. CLEARING CACHES
echo ==================

echo Clearing application caches...
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo ✅ Caches cleared

echo.

REM Step 7: Summary
echo 7. DEPLOYMENT SUMMARY
echo =====================

echo ✅ Esther Ziwa account fix deployment completed
echo.
echo 📊 Files created:
echo    - Diagnosis: %DIAGNOSIS_LOG%
echo    - Fix Log: %FIX_LOG%
echo    - Verification: %VERIFICATION_LOG%
echo.
echo 📝 Next Steps:
echo    1. Review the fix log to confirm success
echo    2. Test Esther's account login and wallet access
echo    3. Inform Esther that her account has been corrected
echo    4. Monitor for any related issues
echo.
echo 🚨 If issues occur:
echo    - Check the log files for details
echo    - Restore from database backup if needed
echo    - Contact technical support
echo.

echo Completed at: %date% %time%
echo === DEPLOYMENT COMPLETE ===

pause