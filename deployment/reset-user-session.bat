@echo off
setlocal enabledelayedexpansion

:: Get project root (parent of deployment folder)
set "SCRIPT_DIR=%~dp0"
set "PROJECT_ROOT=%SCRIPT_DIR%.."

:: Load credentials
if exist "%PROJECT_ROOT%\.deploy-credentials" (
    for /f "usebackq tokens=1,* delims==" %%a in ("%PROJECT_ROOT%\.deploy-credentials") do (
        set "line=%%a"
        if not "!line:~0,1!"=="#" (
            set "%%a=%%~b"
        )
    )
) else (
    echo ❌ Error: .deploy-credentials file not found!
    echo Please create .deploy-credentials file in project root.
    echo Copy from .deploy-credentials.example and fill in your credentials.
    exit /b 1
)

:: Check if user_id argument is provided
if "%~1"=="" (
    echo ❌ Error: User ID is required!
    echo.
    echo Usage: reset-user-session.bat ^<user_id^>
    echo Example: reset-user-session.bat 123
    exit /b 1
)

set "USER_ID=%~1"

echo 🔄 Resetting user session on production...
echo 📍 Server: %DROPLET_IP%
echo 👤 User ID: %USER_ID%
echo.

:: SSH and run the artisan command
ssh %DROPLET_USER%@%DROPLET_IP% "cd %PROJECT_PATH% && php artisan user:reset-session %USER_ID%"

echo.
echo 🎉 Done! User %USER_ID% will need to log in again on production.

endlocal
