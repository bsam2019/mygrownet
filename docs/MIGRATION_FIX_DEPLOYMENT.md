# Migration Fix Deployment Guide

## Issue
Migration failures due to existing tables/columns:
- `transactions` table already exists
- `processed_at` and `processed_by` columns already exist in transactions table

## Solution Applied
Added existence checks to migrations to prevent duplicate creation errors:

### Fixed Migrations:
1. `2024_02_20_000000_create_transactions_table.php` - Added `Schema::hasTable()` check
2. `2024_02_21_000001_add_processed_columns_to_transactions_table.php` - Added `Schema::hasColumn()` checks

## Deployment Instructions

### Step 1: Pull Latest Code
```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
git pull origin main
```

### Step 2: Run Migrations
```bash
php artisan migrate --force
```

If you encounter additional migration errors, use the `--skip-errors` flag:
```bash
php artisan migrate --force 2>&1 | tee migration-log.txt
```

### Step 3: Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache

php artisan optimize
```

### Step 4: Restart PHP-FPM
```bash
sudo systemctl restart php8.2-fpm
```

### Step 5: Test StockFlow Subdomain
```bash
curl -I https://taradasi.mygrownet.com/login
```

Should return `200 OK` status.

## Alternative: Skip Failed Migrations Manually

If a migration continues to fail after adding existence checks, you can manually mark it as run:

```bash
# Connect to MySQL
mysql -u root -p mygrownet

# Insert migration record manually
INSERT INTO migrations (migration, batch) 
VALUES ('2024_02_21_000001_add_processed_columns_to_transactions_table', 
        (SELECT MAX(batch) FROM migrations) + 1);

EXIT;
```

Then run migrations again:
```bash
php artisan migrate --force
```

## Verification

### Check Migration Status
```bash
php artisan migrate:status
```

Look for:
- ✅ All migrations should show as "Ran"
- ❌ No migrations should show as "Pending"

### Check Database Tables
```bash
php artisan tinker

# Verify transactions table exists with all columns
Schema::hasTable('transactions'); // should return true
Schema::hasColumn('transactions', 'processed_at'); // should return true
Schema::hasColumn('transactions', 'processed_by'); // should return true
```

### Test StockFlow Login
1. Visit: https://taradasi.mygrownet.com/login
2. Should see Taradasi Dental Clinic branding
3. Login with: `admin@taradasi.com` / `password`
4. Should redirect to StockFlow dashboard

## If Problems Persist

### Check Logs
```bash
tail -100 storage/logs/laravel.log
```

### Reset Migrations (CAUTION - Development Only!)
**⚠️ DO NOT run this on production unless you have a backup!**

```bash
php artisan migrate:reset
php artisan migrate --seed
```

### Manual Database Check
```bash
mysql -u root -p mygrownet

SHOW TABLES LIKE '%transactions%';
DESCRIBE transactions;
SELECT * FROM migrations ORDER BY id DESC LIMIT 10;
```

## Migration Pattern for Future

When creating migrations, always check for existence:

### For CREATE TABLE:
```php
public function up(): void
{
    if (!Schema::hasTable('table_name')) {
        Schema::create('table_name', function (Blueprint $table) {
            // ...
        });
    }
}
```

### For ALTER TABLE (add columns):
```php
public function up(): void
{
    Schema::table('table_name', function (Blueprint $table) {
        if (!Schema::hasColumn('table_name', 'column_name')) {
            $table->string('column_name')->nullable();
        }
    });
}
```

### For ALTER TABLE (add foreign keys):
```php
public function up(): void
{
    Schema::table('table_name', function (Blueprint $table) {
        if (!Schema::hasColumn('table_name', 'foreign_id')) {
            $table->foreignId('foreign_id')
                  ->nullable()
                  ->constrained('other_table')
                  ->onDelete('set null');
        }
    });
}
```

## Success Criteria
- ✅ All migrations run successfully without errors
- ✅ StockFlow subdomain loads correctly
- ✅ Login works on StockFlow subdomain
- ✅ No database errors in logs

## Timeline
- **Estimated time**: 10-15 minutes
- **Risk level**: Low (only adding existence checks, no schema changes)
- **Rollback**: Simply run `git pull` to revert if needed
