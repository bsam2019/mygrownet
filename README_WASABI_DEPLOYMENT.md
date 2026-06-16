# 🚀 Wasabi Production Deployment

Complete guide and scripts for deploying Wasabi storage to MyGrowNet production.

## 📋 Quick Start

### Windows Users

1. **Deploy to Production:**
   ```cmd
   deploy-wasabi-production.bat
   ```

2. **Verify Setup:**
   ```cmd
   verify-wasabi-setup.bat
   ```

### Linux/Mac Users

1. **Deploy to Production:**
   ```bash
   bash deploy-wasabi-production.sh
   ```

2. **Verify Setup:**
   ```bash
   bash verify-wasabi-setup.sh
   ```

---

## 📁 Files Included

| File | Description |
|------|-------------|
| `deploy-wasabi-production.bat` | Windows deployment script |
| `deploy-wasabi-production.sh` | Linux/Mac deployment script |
| `verify-wasabi-setup.bat` | Windows verification script |
| `verify-wasabi-setup.sh` | Linux/Mac verification script |
| `rollback-to-digitalocean.sh` | Emergency rollback script |
| `WASABI_PRODUCTION_SETUP.md` | Complete documentation |
| `DEPLOY_NOW.md` | Quick deployment guide |

---

## ✅ Pre-Deployment Status

- [x] Content migrated from DigitalOcean Spaces to Wasabi
- [x] Local development tested with Wasabi
- [x] Configuration files updated
- [x] Deployment scripts created
- [x] Verification scripts prepared
- [x] Rollback plan ready

---

## 🎯 What This Does

### Changes Made:
1. Updates `FILESYSTEM_DISK=wasabi` in production .env
2. Adds Wasabi credentials and configuration
3. Clears all Laravel caches
4. Restarts queue workers and services

### No Downtime:
- Zero downtime deployment
- All files already in Wasabi (migrated)
- Old files still in DigitalOcean Spaces (backup)
- Instant rollback available if needed

---

## 💰 Benefits

✅ **50% cost savings** ($15-20/month → $6.99/month)
✅ **Free bandwidth** (no egress charges)
✅ **EU-based storage** (GDPR friendly)
✅ **Better global performance**
✅ **Same S3-compatible API**

---

## 🔒 Safety Features

- Automatic .env backup before changes
- Files exist in both locations
- One-command rollback available
- No data loss possible
- Full monitoring included

---

## 📊 Configuration

### Wasabi Details
```
Region: EU Central 2 (Amsterdam)
Bucket: mygrownet
Endpoint: https://s3.eu-central-2.wasabisys.com
Console: https://console.wasabisys.com
```

### Environment Variables Added
```bash
WASABI_ACCESS_KEY_ID=XQYMRBJVISRWTQDMMC8M
WASABI_SECRET_ACCESS_KEY=C3eRHVJB5nSaCC5lSnhenKTjXGUVUWDA8mKqTuJ0
WASABI_DEFAULT_REGION=eu-central-2
WASABI_BUCKET=mygrownet
WASABI_URL=https://s3.eu-central-2.wasabisys.com/mygrownet
WASABI_ENDPOINT=https://s3.eu-central-2.wasabisys.com
```

---

## 🧪 Testing Checklist

After deployment, test these features:

- [ ] Upload profile picture
- [ ] Upload product image
- [ ] View marketplace listings
- [ ] Upload business document
- [ ] Create GrowBuilder page with images
- [ ] Check all images load correctly

---

## 🆘 Troubleshooting

### Issue: "Access Denied" errors
**Solution:**
```bash
# Check Wasabi credentials in production
ssh sammy@138.197.187.134 "cd /var/www/mygrownet.com && grep WASABI .env"
```

### Issue: Images not loading
**Solution:**
```bash
# Check Laravel logs
ssh sammy@138.197.187.134 "tail -50 /var/www/mygrownet.com/storage/logs/laravel.log"
```

### Issue: Need to rollback
**Solution:**
```bash
bash rollback-to-digitalocean.sh
```

---

## 📞 Support Information

- **Production Server**: 138.197.187.134
- **User**: sammy
- **Project Path**: /var/www/mygrownet.com
- **Wasabi Console**: https://console.wasabisys.com

---

## 🎓 Documentation

For complete details, see:
- `WASABI_PRODUCTION_SETUP.md` - Full technical documentation
- `DEPLOY_NOW.md` - Quick deployment guide

---

## ⏱️ Deployment Timeline

1. **Deploy** (2 minutes) - Update production configuration
2. **Verify** (1 minute) - Test connectivity and uploads
3. **Test** (2 minutes) - Browser testing

**Total: ~5 minutes**

---

## 🎉 Ready to Deploy?

Run the deployment script for your operating system:

**Windows:**
```cmd
deploy-wasabi-production.bat
```

**Linux/Mac:**
```bash
bash deploy-wasabi-production.sh
```

Then verify:

**Windows:**
```cmd
verify-wasabi-setup.bat
```

**Linux/Mac:**
```bash
bash verify-wasabi-setup.sh
```

---

## 📈 Expected Results

After deployment:
- All new uploads stored in Wasabi
- All existing files accessible
- 50% monthly cost savings
- No functionality changes
- Same user experience

---

**Questions?** Check `WASABI_PRODUCTION_SETUP.md` for detailed documentation.

**Issues?** Run the rollback script to revert.

**Success?** Enjoy the cost savings! 💰
