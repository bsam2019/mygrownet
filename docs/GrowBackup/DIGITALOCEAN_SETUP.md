# DigitalOcean Spaces Setup for MyGrowNet Storage

**Last Updated:** February 21, 2026

## Quick Setup Guide

### 1. Create DigitalOcean Spaces Bucket

1. Go to https://cloud.digitalocean.com/spaces
2. Click "Create a Space"
3. Choose settings:
   - **Region**: Choose closest to your users (e.g., `nyc3`, `sgp1`, `fra1`)
   - **Enable CDN**: Optional (can enable later)
   - **Space Name**: `mygrownet-storage` (or your preferred name)
   - **File Listing**: Keep PRIVATE (Restricted)
4. Click "Create a Space"

### 2. Generate Access Keys

1. Go to https://cloud.digitalocean.com/account/api/spaces
2. Click "Generate New Key"
3. Name: `MyGrowNet Storage`
4. Copy both:
   - **Access Key** (like: `DO00ABCDEFGHIJKLMNOP`)
   - **Secret Key** (like: `abcdefghijklmnopqrstuvwxyz1234567890ABCD`)
5. Save these securely - you won't see the secret again!

### 3. Configure Laravel

Add to your `.env` file:

```env
# DigitalOcean Spaces Configuration
AWS_ACCESS_KEY_ID=your_spaces_access_key
AWS_SECRET_ACCESS_KEY=your_spaces_secret_key
AWS_DEFAULT_REGION=nyc3
AWS_BUCKET=mygrownet-storage
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**Important:** Replace:
- `your_spaces_access_key` with your Access Key
- `your_spaces_secret_key` with your Secret Key
- `nyc3` with your chosen region
- `mygrownet-storage` with your Space name

### 4. Available Regions

| Region Code | Location |
|-------------|----------|
| `nyc3` | New York 3 |
| `sfo3` | San Francisco 3 |
| `sgp1` | Singapore 1 |
| `fra1` | Frankfurt 1 |
| `ams3` | Amsterdam 3 |
| `blr1` | Bangalore 1 |
| `syd1` | Sydney 1 |

### 5. Test Connection

```bash
php artisan tinker
```

```php
// Test write
Storage::disk('s3')->put('test.txt', 'Hello from MyGrowNet!');

// Test read
Storage::disk('s3')->exists('test.txt'); // Should return true

// Test delete
Storage::disk('s3')->delete('test.txt');

// If all work, you're good to go!
```

### 6. Provision Test User

Create a storage subscription for testing:

```bash
php artisan tinker
```

```php
use App\Models\User;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;

// Get your user
$user = User::where('email', 'your@email.com')->first();

// Get starter plan
$plan = StoragePlan::where('slug', 'starter')->first();

// Create subscription
UserStorageSubscription::create([
    'user_id' => $user->id,
    'storage_plan_id' => $plan->id,
    'status' => 'active',
    'start_at' => now(),
    'source' => 'manual',
]);

echo "Storage subscription created for {$user->name}!";
```

## Pricing

DigitalOcean Spaces pricing (as of 2024):
- **Storage**: $5/month for 250 GB
- **Bandwidth**: $0.01/GB after first 1 TB
- **No egress fees** for first 1 TB/month

Perfect for development and small-scale production!

## CORS Configuration (Optional)

If you need browser uploads directly to Spaces:

1. Go to your Space in DigitalOcean
2. Click "Settings" tab
3. Scroll to "CORS Configurations"
4. Add:

```json
[
  {
    "AllowedOrigins": ["https://yourdomain.com", "http://localhost"],
    "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
    "AllowedHeaders": ["*"],
    "MaxAgeSeconds": 3000
  }
]
```

## Troubleshooting

### Error: "Could not connect to S3"

**Check:**
1. Access keys are correct in `.env`
2. Region matches your Space region
3. Endpoint URL is correct: `https://{region}.digitaloceanspaces.com`

### Error: "Access Denied"

**Check:**
1. Space is set to PRIVATE (not public)
2. Access keys have proper permissions
3. Bucket name is correct

### Error: "Bucket not found"

**Check:**
1. `AWS_BUCKET` matches your Space name exactly
2. Region in endpoint matches Space region

### Files not uploading

**Check:**
1. Run `composer require league/flysystem-aws-s3-v3` if not installed
2. Clear config cache: `php artisan config:clear`
3. Check Space has available storage

## Security Best Practices

1. **Never commit** `.env` file to git
2. **Rotate keys** regularly (every 90 days)
3. **Use separate Spaces** for dev/staging/production
4. **Enable CDN** for production (improves performance)
5. **Set up monitoring** in DigitalOcean dashboard

## Migration to Wasabi Later

When ready to switch to Wasabi:

1. Create Wasabi bucket
2. Update `.env`:
   ```env
   AWS_ENDPOINT=https://s3.wasabisys.com
   AWS_DEFAULT_REGION=us-east-1
   ```
3. Migrate files using `aws s3 sync` or similar tool
4. Update DNS if using custom domain

The code is S3-compatible, so switching is just configuration!

## Next Steps

1. ✅ Configure DigitalOcean Spaces
2. ✅ Test connection
3. ✅ Provision test user
4. 🚀 Start testing uploads via UI
5. 📊 Monitor usage in DigitalOcean dashboard

## Support

- DigitalOcean Docs: https://docs.digitalocean.com/products/spaces/
- Community: https://www.digitalocean.com/community/tags/spaces
- Support: https://cloud.digitalocean.com/support
