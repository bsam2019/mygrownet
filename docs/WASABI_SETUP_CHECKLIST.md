# Wasabi Setup Checklist

## Current Status: ❌ Connection Failed (403 Access Denied)

The Wasabi configuration has been added to your Laravel application, but the connection test is failing with a 403 Access Denied error.

## Troubleshooting Steps

### Step 1: Verify Bucket Exists

1. Log in to Wasabi Console: https://console.wasabisys.com/
2. Navigate to **Buckets**
3. Check if bucket named **mygrownet** exists
4. If not, create it:
   - Click **Create Bucket**
   - Bucket Name: `mygrownet`
   - Region: Choose closest to your users (e.g., `us-east-1` for North America/Global)
   - Click **Create Bucket**

### Step 2: Verify Bucket Region

If the bucket exists, check its region:

1. In Wasabi Console, click on the **mygrownet** bucket
2. Note the region (e.g., `us-east-1`, `us-west-1`, `eu-central-1`)
3. Update `.env` file with the correct region:

```env
# Common Wasabi Regions:
# us-east-1: US East (N. Virginia)
# us-east-2: US East (N. Virginia) 
# us-west-1: US West (Oregon)
# eu-central-1: Europe (Amsterdam)
# eu-central-2: Europe (Frankfurt)
# ap-northeast-1: Asia Pacific (Tokyo)
# ap-northeast-2: Asia Pacific (Osaka)

WASABI_DEFAULT_REGION=us-east-1  # <- Update this to match your bucket region
```

### Step 3: Verify Access Keys

1. In Wasabi Console, go to **Access Keys**
2. Verify the access key ID: `QVKYILFZQAL9Z0WTABZK`
3. Check if the key is **Active** (not disabled)
4. Verify the key has proper permissions:
   - Should have **Full Access** or at minimum **Read/Write** access to the bucket

### Step 4: Check Bucket Policy (Public Access)

1. In Wasabi Console, open the **mygrownet** bucket
2. Go to **Policies** tab
3. Set bucket policy for public read access:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::mygrownet/*"
    }
  ]
}
```

**Note:** This allows public read access to files (needed for images/videos). Write access still requires valid credentials.

### Step 5: Update Region-Specific Endpoint

Once you know the correct region, update `.env`:

```env
# For us-east-1 (N. Virginia):
WASABI_DEFAULT_REGION=us-east-1
WASABI_ENDPOINT=https://s3.us-east-1.wasabisys.com
WASABI_URL=https://s3.us-east-1.wasabisys.com/mygrownet

# For us-west-1 (Oregon):
WASABI_DEFAULT_REGION=us-west-1
WASABI_ENDPOINT=https://s3.us-west-1.wasabisys.com
WASABI_URL=https://s3.us-west-1.wasabisys.com/mygrownet

# For eu-central-1 (Amsterdam):
WASABI_DEFAULT_REGION=eu-central-1
WASABI_ENDPOINT=https://s3.eu-central-1.wasabisys.com
WASABI_URL=https://s3.eu-central-1.wasabisys.com/mygrownet
```

### Step 6: Test Connection Again

After making corrections:

```bash
php artisan config:clear
php artisan storage:test-connection wasabi
```

Expected output when successful:
```
Testing wasabi...
  ✓ Write test passed
  ✓ Read test passed
  ✓ URL generation passed: https://...
  ✓ Delete test passed
✓ wasabi connection successful!
```

---

## Current Configuration

**Credentials Configured:**
- Access Key: `QVKYILFZQAL9Z0WTABZK`
- Secret Key: `zJKdQVvFbeesHgtkOErvIIocfSiiDH09dwUJCw4B` (configured)
- Bucket: `mygrownet`
- Region: `us-east-1`
- Endpoint: `https://s3.wasabisys.com`

**Migration Mode:** `do_spaces_only` (not migrating yet - safe)

---

## Common Issues

### Issue: Bucket doesn't exist
**Solution:** Create the bucket in Wasabi Console with the exact name `mygrownet`

### Issue: Wrong region
**Solution:** Update `.env` with the correct region from Wasabi Console

### Issue: Access key disabled or expired
**Solution:** Check Access Keys page in Wasabi Console, create new key if needed

### Issue: Insufficient permissions
**Solution:** Ensure access key has Read/Write permissions for the bucket

### Issue: Bucket in different region than endpoint
**Solution:** Match the `WASABI_DEFAULT_REGION` and `WASABI_ENDPOINT` to your bucket's region

---

## Next Steps (After Successful Connection)

Once the Wasabi connection test passes:

1. **Phase 1: Test Uploads** (1-2 days)
   ```bash
   # Test a single file upload to Wasabi
   php artisan tinker
   >>> Storage::disk('wasabi')->put('test.txt', 'Hello Wasabi');
   >>> Storage::disk('wasabi')->url('test.txt');
   ```

2. **Phase 2: Enable Dual-Write Mode** (Week 1-2)
   ```env
   STORAGE_MIGRATION_MODE=dual_write
   ```
   - New files will be written to both DO Spaces and Wasabi
   - Reads still from DO Spaces (primary)

3. **Phase 3: Migrate Existing Files** (Week 2-3)
   ```bash
   # Dry run first
   php artisan storage:migrate-to-wasabi --dry-run
   
   # Actual migration with verification
   php artisan storage:migrate-to-wasabi --verify --batch-size=50
   ```

4. **Phase 4: Switch to Wasabi Primary** (Week 3-4)
   ```env
   STORAGE_MIGRATION_MODE=dual_write
   STORAGE_PRIMARY_DISK=wasabi
   ```

5. **Phase 5: Wasabi Only** (Week 4+)
   ```env
   STORAGE_MIGRATION_MODE=wasabi_only
   FILESYSTEM_DISK=wasabi
   ```

---

## Support

If you continue to have issues:

1. **Check Wasabi Status:** https://status.wasabi.com/
2. **Wasabi Support:** support@wasabi.com
3. **Documentation:** https://docs.wasabi.com/

**Current Error:**
```
403 Access Denied
Error: AccessDenied
Message: Access Denied
```

This typically means:
- ✗ Bucket doesn't exist
- ✗ Wrong region
- ✗ Access key lacks permissions
- ✗ Access key is disabled

**Action Required:** Please check Steps 1-4 above in the Wasabi Console.
