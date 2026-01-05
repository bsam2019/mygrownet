# Resume GrowBuilder Subdomain Setup

**Status:** Server Down - Resume When Available  
**Date:** January 5, 2026

## What's Been Completed ✅

1. ✅ Laravel subdomain routing implemented
2. ✅ Nginx wildcard configuration deployed
3. ✅ DNS verified (Cloudflare wildcard working)
4. ✅ Code committed and pushed to GitHub
5. ✅ Cloudflare API token obtained

## What's Remaining ⏳

**Only 1 step left:** Install wildcard SSL certificate

## When Server is Back Up

### Quick Command to Complete Setup:

```bash
# 1. Upload and run the SSL setup script
scp setup-wildcard-ssl.sh sammy@138.197.187.134:/var/www/mygrownet.com/
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
chmod +x setup-wildcard-ssl.sh
bash setup-wildcard-ssl.sh
```

**That's it!** The script will:
- Install Cloudflare DNS plugin
- Create credentials file with API token
- Request wildcard certificate for `*.mygrownet.com`
- Reload Nginx
- Verify everything works

### Expected Output:

```
Setting up Wildcard SSL Certificate for *.mygrownet.com
========================================================

1. Installing Cloudflare DNS plugin...
   ✓ Plugin installed

2. Creating credentials directory...
   ✓ Directory created

3. Creating Cloudflare credentials file...
   ✓ Credentials file created and secured

4. Requesting wildcard SSL certificate...
   This may take a few minutes...
   
   ✓ Wildcard SSL certificate obtained successfully!

5. Verifying certificate...
   [Certificate details]

6. Testing Nginx configuration...
   nginx: configuration file test is successful

7. Reloading Nginx...
   ✓ Nginx reloaded

✓ Wildcard SSL certificate setup complete!
```

### Test After Setup:

1. Create a test site in GrowBuilder with subdomain "test"
2. Visit: `https://test.mygrownet.com`
3. Should load without SSL errors ✓

---

## Files Ready to Deploy

- `setup-wildcard-ssl.sh` - SSL certificate setup script
- `update-nginx-subdomain.sh` - Nginx config (already deployed)
- All Laravel code already on server via git

---

## Cloudflare API Token

**Token:** `CKVBSMHrBRnVKTTYEi1EZmR8gSTgiedbWPhHRBqd`  
**Permissions:** Edit zone DNS for mygrownet.com  
**Stored in:** `setup-wildcard-ssl.sh` (will be saved to `/root/.secrets/cloudflare.ini`)

---

## Rollback (if needed)

If anything goes wrong:
```bash
sudo cp /etc/nginx/sites-available/mygrownet.com.backup.subdomain \
  /etc/nginx/sites-available/mygrownet.com
sudo nginx -t && sudo systemctl reload nginx
```

---

## Documentation

- `GROWBUILDER_SUBDOMAIN_STATUS.md` - Full implementation status
- `GROWBUILDER_SUBDOMAIN_SETUP.md` - Complete setup guide
- `FIREBASE_SETUP.md` - Firebase & notification system (completed)

---

## Summary

**What we accomplished today:**
1. Fixed all scheduler errors (Firebase, subscriptions, LifePlus)
2. Started Reverb WebSocket server
3. Added 28 site permissions for GrowBuilder
4. Implemented GrowBuilder subdomain routing (95% complete)

**What's left:** 
- Run `setup-wildcard-ssl.sh` when server is back (5 minutes)

**Then GrowBuilder sites will work at:**
- `https://yoursite.mygrownet.com` ✓
