@echo off
setlocal enabledelayedexpansion

REM Get script directory and project root
set "SCRIPT_DIR=%~dp0"
set "PROJECT_ROOT=%SCRIPT_DIR%.."
set "CREDENTIALS_FILE=%PROJECT_ROOT%\.deploy-credentials"
set "EXAMPLE_FILE=%PROJECT_ROOT%\.deploy-credentials.example"

echo 🔐 Deployment Credentials Setup
echo ================================

REM Check if credentials file exists
if exist "%CREDENTIALS_FILE%" (
    echo ✅ Credentials file already exists at: %CREDENTIALS_FILE%
    echo.
    echo Current configuration:
    echo ---------------------
    
    REM Read current credentials
    for /f "tokens=1,2 delims==" %%a in ('type "%CREDENTIALS_FILE%" ^| findstr /v "^#" ^| findstr "="') do (
        set "%%a=%%b"
        set "%%a=!%%a:"=!"
    )
    
    echo Droplet IP: !DROPLET_IP!
    echo Droplet User: !DROPLET_USER!
    echo GitHub Username: !GITHUB_USERNAME!
    echo Project Path: !PROJECT_PATH!
    echo.
    
    set /p "recreate=Do you want to recreate the credentials file? (y/N): "
    if /i not "!recreate!"=="y" (
        echo Keeping existing credentials file.
        exit /b 0
    )
)

REM Check if example file exists
if not exist "%EXAMPLE_FILE%" (
    echo ❌ Error: Example file not found at %EXAMPLE_FILE%
    exit /b 1
)

echo.
echo 📝 Please enter your deployment credentials:
echo.

set /p "droplet_ip=Droplet IP: "
set /p "droplet_user=Droplet User (default: sammy): "
if "!droplet_user!"=="" set "droplet_user=sammy"

echo Droplet Sudo Password (input will be hidden):
powershell -Command "$password = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($password))" > temp_password.txt
set /p droplet_password=<temp_password.txt
del temp_password.txt

set /p "github_username=GitHub Username: "

echo GitHub Token (input will be hidden):
powershell -Command "$token = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($token))" > temp_token.txt
set /p github_token=<temp_token.txt
del temp_token.txt

set /p "project_path=Project Path (default: /var/www/mygrownet.com): "
if "!project_path!"=="" set "project_path=/var/www/mygrownet.com"

REM Create credentials file
(
echo # Deployment Credentials
echo # This file is ignored by git - DO NOT commit to repository
echo.
echo # Droplet SSH
echo DROPLET_IP="!droplet_ip!"
echo DROPLET_USER="!droplet_user!"
echo DROPLET_SUDO_PASSWORD="!droplet_password!"
echo.
echo # GitHub
echo GITHUB_USERNAME="!github_username!"
echo GITHUB_TOKEN="!github_token!"
echo.
echo # Project Path
echo PROJECT_PATH="!project_path!"
) > "%CREDENTIALS_FILE%"

echo.
echo ✅ Credentials file created successfully!
echo 📁 Location: %CREDENTIALS_FILE%
echo.
echo You can now run deployment scripts:
echo   deployment\deploy.bat
echo   deployment\deploy-with-migration.bat
echo   deployment\deploy-with-seeder.bat
echo   deployment\deploy-with-assets.bat

pause