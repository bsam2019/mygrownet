# Domain Migration Setup - Ready to Execute

## Current Status

✅ **Preparation Complete**
- Domain migration scripts created
- Nginx configuration prepared
- Backup procedures in place
- Documentation complete

⚠️ **Action Required**
The automated script encountered sudo password authentication issues in non-interactive mode. You need to complete the setup manually via SSH.

## Quick Start - Choose Your Method

### Method 1: Manual Step-by-Step (Recommended)
Follow the detailed guide: **`MANUAL_DOMAIN_SETUP.md`**

This provides:
- Step-by-step instructions with explanations
- Verification steps
- Troubleshooting guide
- Rollback procedures

### Method 2: Copy-Paste Commands
Use the command script: **`deployment/server-commands.sh`**

1. SSH into server:
   ```bash
   ssh sammy@138.197.187.134
   ```

2. Copy and paste the commands from `deployment/server-commands.sh` one section at a time

## What Needs to Be Done

### On the Server (via SSH):

1. **Update Nginx Configuration**
   - Change server_name from `mygrownet.edulinkzm.com` to `mygrownet.com`
   - Update SSL certificate paths
   - Test and reload nginx

2. **Update Laravel .env**
   - Change `APP_URL` to `https://mygrownet.com`
   - Add/update `ASSET_URL` to `https://mygrownet.com`

3. **Clear Laravel Caches**
   - Config, route, view caches

4. **Fix Permissions**
   - Ensure www-data owns storage and cache directories

5. **Setup SSL Certificates**
   - Run Certbot for mygrownet.com and www.mygrownet.com
   - **IMPORTANT:** DNS must be propagated first!

## DNS Requirements

Before running SSL setup, ensure DNS records are configured:

```
Type: A
Name: mygrownet.com
Value: 138.197.187.134

Type: A  
Name: www.mygrownet.com
Value: 138.197.187.134
```

**Verify DNS propagation:**
```bash
dig mygrownet.com
dig www.mygrownet.com
```

Both should return: `138.197.187.134`

## Server Credentials

**Server IP:** 138.197.187.134  
**SSH User:** sammy  
**SSH Password:** Bsam@2025!!  
**App Path:** /var/www/mygrownet.com

## Expected Outcome

After completion:
- ✅ https://mygrownet.com - Working with valid SSL
- ✅ https://www.mygrownet.com - Working with valid SSL
- ✅ HTTP automatically redirects to HTTPS
- ✅ All Laravel caches optimized
- ✅ Proper file permissions set

## Files Created

1. **MANUAL_DOMAIN_SETUP.md** - Detailed step-by-step guide
2. **deployment/server-commands.sh** - Copy-paste command script
3. **deployment/update-server-config.sh** - Automated script (has sudo issues)
4. **DOMAIN_MIGRATION_GUIDE.md** - Complete migration documentation
5. **This file** - Quick reference

## Estimated Time

- Manual setup: 10-15 minutes
- SSL certificate setup: 2-5 minutes (after DNS propagation)
- Total: ~15-20 minutes

## Support

If you encounter issues:

**Check Nginx:**
```bash
sudo nginx -t
sudo systemctl status nginx
sudo tail -f /var/log/nginx/error.log
```

**Check PHP-FPM:**
```bash
sudo systemctl status php8.3-fpm
```

**Check Laravel:**
```bash
tail -f /var/www/mygrownet.com/storage/logs/laravel.log
```

**Rollback:**
```bash
sudo cp /etc/nginx/sites-available/mygrownet.com.backup /etc/nginx/sites-available/mygrownet.com
sudo nginx -t
sudo systemctl reload nginx
```

## Next Steps

1. **Verify DNS is propagated** (if not done already)
2. **SSH into the server**
3. **Follow MANUAL_DOMAIN_SETUP.md** or use commands from **deployment/server-commands.sh**
4. **Test the site** at https://mygrownet.com
5. **Verify SSL certificate** is valid

---

**Ready to proceed?** Open `MANUAL_DOMAIN_SETUP.md` and follow the steps!
