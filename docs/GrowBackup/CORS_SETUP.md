# DigitalOcean Spaces CORS Configuration

**Last Updated:** February 22, 2026

## Problem

When uploading files, you see:
- ❌ "Network Error" in the upload progress
- ✅ File appears in the list (created in database)
- ❌ File is not actually uploaded to storage

This happens because the browser is blocked by CORS (Cross-Origin Resource Sharing) policy when trying to upload directly to DigitalOcean Spaces.

## Solution

Configure CORS on your DigitalOcean Space to allow browser uploads.

## Quick Fix (Recommended)

Use the built-in Laravel commands:

```bash
# Check current CORS configuration
php artisan storage:test-cors

# Apply CORS configuration automatically
php artisan storage:apply-cors
```

This will configure CORS for your current app URL and common development URLs.

## Manual Configuration

If you prefer to configure CORS manually through the DigitalOcean UI or AWS CLI, follow the steps below.

## Steps

### 1. Go to DigitalOcean Spaces

1. Log in to DigitalOcean
2. Navigate to **Spaces** in the left sidebar
3. Click on your space (`mygrownet`)

### 2. Configure CORS Settings

1. Click on the **Settings** tab
2. Scroll down to **CORS Configurations**
3. Click **Add CORS Configuration**

### 3. Add CORS Rule

Add the following configuration:

```json
{
  "AllowedOrigins": ["http://127.0.0.1:8001", "http://localhost:8001", "http://127.0.0.1:8000", "http://localhost:8000", "https://yourdomain.com"],
  "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
  "AllowedHeaders": ["*"],
  "ExposeHeaders": ["ETag"],
  "MaxAgeSeconds": 3000
}
```

**Important:** Replace `https://yourdomain.com` with your actual domain when deploying to production.

### 4. Save Configuration

Click **Save** to apply the CORS configuration.

## Alternative: Using AWS CLI

If you prefer using the command line:

1. Install AWS CLI
2. Configure it with your DigitalOcean credentials
3. Create a file `cors.json`:

```json
{
  "CORSRules": [
    {
      "AllowedOrigins": ["http://127.0.0.1:8001", "http://localhost:8001", "http://127.0.0.1:8000", "http://localhost:8000"],
      "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
      "AllowedHeaders": ["*"],
      "ExposeHeaders": ["ETag"],
      "MaxAgeSeconds": 3000
    }
  ]
}
```

4. Apply the configuration:

```bash
aws s3api put-bucket-cors \
  --bucket mygrownet \
  --cors-configuration file://cors.json \
  --endpoint-url https://nyc3.digitaloceanspaces.com
```

## Testing

After configuring CORS:

1. **Test CORS configuration:**
   ```bash
   php artisan storage:test-cors
   ```

2. **Clear your browser cache:**
   - Hard refresh: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)

3. **Try uploading a file:**
   - Upload should show progress bar (10% → 90% → 100%)
   - Status should change: Pending → Uploading → Completing → Completed
   - File should be downloadable
   - No "Network Error" message

If you see progress moving from 10% → 90%, CORS is working!

## Troubleshooting

### Still getting Network Error?

1. **Check origin URL matches exactly** (including port)
   - Development: `http://127.0.0.1:8000` or `http://localhost:8000`
   - Make sure the port matches your Laravel server
   
2. **Verify CORS was saved**
   - Go back to Spaces → Settings → CORS Configurations
   - Confirm your rule is listed
   
3. **Clear browser cache**
   - Hard refresh: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
   - Or clear cache in browser settings
   
4. **Check browser console for specific CORS errors**
   - Open DevTools (F12)
   - Go to Console tab
   - Look for red CORS error messages
   - Share the exact error message if still stuck

### CORS not available in DigitalOcean UI?

Use the AWS CLI method instead - DigitalOcean Spaces is S3-compatible.

### Files show as uploaded but aren't?

This means:
- ✅ Step 1 worked: File record created in database
- ❌ Step 2 failed: Upload to S3 blocked by CORS
- ❌ Step 3 skipped: Upload never completed

**Solution:** Configure CORS as described above.

### How to verify CORS is working?

After configuring CORS, upload a small test file:
1. Upload should show progress bar (0% → 100%)
2. Status should change: Pending → Uploading → Finalizing → Completed
3. File should be downloadable
4. No "Network Error" message

If you see progress moving from 10% → 90%, CORS is working!
