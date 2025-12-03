# Maintenance Mode Guide

## Quick Reference

### Enable Maintenance Mode (with Admin Bypass)
```bash
bash deployment/maintenance-mode.sh
```

This will:
- Put the site in maintenance mode
- Generate a secret bypass URL for admins
- Display the bypass URL in the terminal (keep it private!)

### Disable Maintenance Mode
```bash
bash deployment/maintenance-off.sh
```

---

## How Admin Bypass Works

When you enable maintenance mode, the script generates a unique secret token. 

**Example output:**
```
✅ Maintenance mode enabled

🔑 Admin Bypass URL:
   https://mygrownet.com/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0

💡 Visit this URL to bypass maintenance mode as admin
   (Keep this URL private!)
```

### Using the Bypass URL

1. Copy the bypass URL from the terminal
2. Visit the URL in your browser
3. A cookie will be set allowing you to access the site
4. You can now navigate normally while others see the maintenance page

### Security Notes

- The secret token is randomly generated each time
- Only share the bypass URL with trusted admins
- The bypass cookie expires when maintenance mode is disabled
- Regular users will see a "503 Service Unavailable" page

---

## Manual Commands (if needed)

### Enable with custom secret
```bash
ssh user@server
cd /var/www/mygrownet.com
php artisan down --secret="your-custom-secret" --retry=60
```

### Check if maintenance mode is active
```bash
php artisan | grep "down"
```

### Disable maintenance mode
```bash
php artisan up
```

---

## Troubleshooting

**Can't access site even with bypass URL?**
- Clear browser cookies and try again
- Make sure you copied the full URL including the token
- Check if maintenance mode is actually enabled: `php artisan | grep down`

**Need to change the bypass token?**
- Disable and re-enable maintenance mode to generate a new token

**Users seeing cached maintenance page?**
- The `--retry=60` flag tells browsers to retry after 60 seconds
- Users may need to hard refresh (Ctrl+F5)
