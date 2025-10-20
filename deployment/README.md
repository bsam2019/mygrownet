# MyGrowNet Deployment Scripts

## Setup

1. Ensure `.deploy-credentials` file exists in project root (already created and gitignored)
2. Make scripts executable: `chmod +x deployment/*.sh`

## Available Scripts

### 1. `deploy.sh` - Standard Deployment

Pulls latest code, fixes permissions, and optimizes cache.

```bash
bash deployment/deploy.sh
```

**Use when:**

- Deploying code changes
- No database changes needed

---

### 2. `deploy-with-migration.sh` - Deploy with Migrations

Pulls code, runs migrations, fixes permissions, and optimizes.

```bash
bash deployment/deploy-with-migration.sh
```

**Use when:**

- Database schema changes
- New migrations added

---

### 3. `deploy-with-seeder.sh` - Deploy with Seeder

Pulls code, runs migrations, seeds database, and optimizes.

```bash
bash deployment/deploy-with-seeder.sh
```

**Use when:**

- Need to seed production data
- Adding new default data (packages, achievements, etc.)

---

### 4. `deploy-with-assets.sh` - Deploy with Frontend Assets

Builds frontend assets locally, pulls code, uploads assets, and optimizes.

```bash
bash deployment/deploy-with-assets.sh
```

**Use when:**

- Frontend changes (Vue, CSS, JavaScript)
- Updated UI components or styles
- Need to rebuild and deploy compiled assets

---

## What Each Script Does

All scripts:

1. Load credentials from `.deploy-credentials`
2. SSH into droplet
3. Pull latest code from GitHub
4. Fix Laravel permissions (storage, cache)
5. Clear and optimize Laravel cache

Additional steps:

- **deploy-with-migration.sh**: Runs `php artisan migrate --force`
- **deploy-with-seeder.sh**: Runs migrations + `php artisan db:seed --class=ProductionSeeder`
- **deploy-with-assets.sh**: Builds assets locally (`npm run build`) + uploads to droplet

## Credentials File

The `.deploy-credentials` file in project root contains:

- Droplet IP and SSH credentials
- GitHub token for pulling
- Project path

**Note:** This file is gitignored and will not be committed to the repository.
