@echo off
echo Clearing Laravel caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo.
echo Regenerating Composer autoload...
composer dump-autoload

echo.
echo Done! All caches cleared.
echo Please restart your development server now.
pause
