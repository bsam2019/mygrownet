# MyGrowNet Storage - Quick Start Guide

**Last Updated:** February 21, 2026

## 🚀 Get Started in 5 Minutes

### Step 1: Configure DigitalOcean Spaces (2 minutes)

1. **Create Space:**
   - Go to https://cloud.digitalocean.com/spaces
   - Click "Create a Space"
   - Name: `mygrownet-storage`
   - Region: Choose closest to you (e.g., `nyc3`)
   - Keep PRIVATE

2. **Generate Keys:**
   - Go to https://cloud.digitalocean.com/account/api/spaces
   - Click "Generate New Key"
   - Copy Access Key and Secret Key

3. **Update `.env`:**
   ```env
   AWS_ACCESS_KEY_ID=your_access_key_here
   AWS_SECRET_ACCESS_KEY=your_secret_key_here
   AWS_DEFAULT_REGION=nyc3
   AWS_BUCKET=mygrownet-storage
   AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
   AWS_USE_PATH_STYLE_ENDPOINT=false
   ```

### Step 2: Test Connection (30 seconds)

```bash
php artisan tinker
```

```php
Storage::disk('s3')->put('test.txt', 'Hello!');
Storage::disk('s3')->exists('test.txt'); // Should return true
Storage::disk('s3')->delete('test.txt');
exit
```

If all commands work, you're connected! ✅

### Step 3: Provision Your User (30 seconds)

```bash
php artisan storage:provision-user your@email.com
```

This gives you a Starter plan (2GB storage).

### Step 4: Access Storage UI (1 minute)

1. Login to your MyGrowNet account
2. Navigate to `/storage` in your browser
3. You should see:
   - Empty file manager
   - Usage indicator showing 0 GB / 2 GB
   - Upload button

### Step 5: Test Upload

1. Click "Upload" button
2. Select a small file (image, PDF, etc.)
3. Watch the progress bar
4. File should appear in your storage!

## 🎉 You're Done!

Your storage is now fully functional. Try:
- Creating folders
- Uploading files
- Downloading files
- Renaming items
- Deleting items

## Common Commands

```bash
# Provision user with different plan
php artisan storage:provision-user user@email.com --plan=basic

# Check storage plans
php artisan tinker
\App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan::all(['name', 'slug']);

# Check user's subscription
\App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription::with('storagePlan')->where('user_id', 1)->first();

# Check usage
\App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage::find(1);
```

## Troubleshooting

### "No active storage subscription found"
```bash
php artisan storage:provision-user your@email.com
```

### "Storage quota exceeded"
```bash
# Upgrade to larger plan
php artisan storage:provision-user your@email.com --plan=basic
```

### "Could not connect to S3"
- Check `.env` credentials
- Verify Space name matches `AWS_BUCKET`
- Ensure region in endpoint matches Space region

### Upload fails
- Check file size doesn't exceed plan limit
- Verify S3 connection works (Step 2)
- Check browser console for errors

## Next Steps

- **Add more users**: Run provision command for each user
- **Upgrade plans**: Use `--plan=basic`, `--plan=growth`, or `--plan=pro`
- **Monitor usage**: Check DigitalOcean dashboard
- **Production**: Switch to Wasabi when ready (see DIGITALOCEAN_SETUP.md)

## Support

- Setup Issues: See `docs/GrowBackup/DIGITALOCEAN_SETUP.md`
- API Reference: See `docs/GrowBackup/IMPLEMENTATION_STATUS.md`
- Full Guide: See `docs/GrowBackup/SETUP_GUIDE.md`
