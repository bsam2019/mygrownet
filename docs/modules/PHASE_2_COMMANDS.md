# Phase 2: Infrastructure Layer - Command Reference

**Last Updated:** December 1, 2025  
**Status:** Ready to Execute

---

## Quick Start Commands

### 1. Create Migrations

```bash
# Create modules table
php artisan make:migration create_modules_table

# Create module subscriptions table
php artisan make:migration create_module_subscriptions_table

# Create module access logs table
php artisan make:migration create_module_access_logs_table

# Create user module settings table
php artisan make:migration create_user_module_settings_table

# Create module team access table (for SME multi-user)
php artisan make:migration create_module_team_access_table
```

### 2. Run Migrations

```bash
# Run all migrations
php artisan migrate

# Rollback if needed
php artisan migrate:rollback

# Fresh migration (drops all tables)
php artisan migrate:fresh
```

### 3. Create Seeders

```bash
# Create module seeder
php artisan make:seeder ModuleSeeder

# Run seeder
php artisan db:seed --class=ModuleSeeder

# Run all seeders
php artisan db:seed
```

### 4. Create Configuration

```bash
# Create config file manually
# config/modules.php
```

### 5. Create Service Provider

```bash
# Create module service provider
php artisan make:provider ModuleServiceProvider

# Register in bootstrap/providers.php
```

### 6. Test Database Connection

```bash
# Check database connection
php artisan db:show

# List tables
php artisan db:table modules
```

---

## File Creation Checklist

### Migrations (database/migrations/)
- [ ] `YYYY_MM_DD_create_modules_table.php`
- [ ] `YYYY_MM_DD_create_module_subscriptions_table.php`
- [ ] `YYYY_MM_DD_create_module_access_logs_table.php`
- [ ] `YYYY_MM_DD_create_user_module_settings_table.php`
- [ ] `YYYY_MM_DD_create_module_team_access_table.php`

### Eloquent Models (app/Infrastructure/Persistence/Eloquent/)
- [ ] `ModuleModel.php`
- [ ] `ModuleSubscriptionModel.php`
- [ ] `ModuleAccessLogModel.php`
- [ ] `UserModuleSettingModel.php`
- [ ] `ModuleTeamAccessModel.php`

### Repositories (app/Infrastructure/Persistence/Repositories/)
- [ ] `EloquentModuleRepository.php`
- [ ] `EloquentModuleSubscriptionRepository.php`

### Configuration (config/)
- [ ] `modules.php`

### Seeders (database/seeders/)
- [ ] `ModuleSeeder.php`

### Service Provider (app/Providers/)
- [ ] `ModuleServiceProvider.php`

---

## Migration Templates

### modules Table

```php
Schema::create('modules', function (Blueprint $table) {
    $table->string('id', 50)->primary();
    $table->string('name', 100);
    $table->string('slug', 50)->unique();
    $table->enum('category', ['core', 'personal', 'sme', 'enterprise']);
    $table->text('description')->nullable();
    $table->string('icon', 50)->nullable();
    $table->string('color', 50)->nullable();
    $table->string('thumbnail', 255)->nullable();
    
    // Access control
    $table->json('account_types');
    $table->json('required_roles')->nullable();
    $table->integer('min_user_level')->nullable();
    
    // Configuration
    $table->json('routes');
    $table->json('pwa_config')->nullable();
    $table->json('features')->nullable();
    $table->json('subscription_tiers')->nullable();
    $table->boolean('requires_subscription')->default(true);
    
    // Metadata
    $table->string('version', 20)->default('1.0.0');
    $table->enum('status', ['active', 'beta', 'coming_soon', 'inactive'])->default('active');
    $table->timestamps();
    
    $table->index('category');
    $table->index('status');
});
```

### module_subscriptions Table

```php
Schema::create('module_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('module_id', 50);
    $table->string('subscription_tier', 50);
    
    // Status
    $table->enum('status', ['active', 'trial', 'suspended', 'cancelled'])->default('active');
    
    // Dates
    $table->timestamp('started_at');
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    
    // Billing
    $table->boolean('auto_renew')->default(true);
    $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('ZMW');
    
    // Limits (for SME apps)
    $table->integer('user_limit')->nullable();
    $table->integer('storage_limit_mb')->nullable();
    
    $table->timestamps();
    
    $table->foreign('module_id')->references('id')->on('modules');
    $table->unique(['user_id', 'module_id']);
    $table->index('status');
    $table->index('expires_at');
});
```

---

## Seeder Template

```php
// database/seeders/ModuleSeeder.php
public function run(): void
{
    DB::table('modules')->insert([
        [
            'id' => 'core',
            'name' => 'MyGrowNet Core',
            'slug' => 'core',
            'category' => 'core',
            'description' => 'Core MLM platform features',
            'icon' => 'home',
            'color' => 'blue',
            'account_types' => json_encode(['member']),
            'routes' => json_encode([
                'integrated' => '/dashboard',
                'standalone' => null,
            ]),
            'pwa_config' => json_encode([
                'enabled' => false,
            ]),
            'features' => json_encode([
                'offline' => false,
                'dataSync' => false,
            ]),
            'requires_subscription' => false,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 'sme-accounting',
            'name' => 'SME Accounting',
            'slug' => 'accounting',
            'category' => 'sme',
            'description' => 'Complete accounting solution for SMEs',
            'icon' => 'calculator',
            'color' => 'purple',
            'account_types' => json_encode(['business']),
            'routes' => json_encode([
                'integrated' => '/modules/accounting',
                'standalone' => '/apps/accounting',
            ]),
            'pwa_config' => json_encode([
                'enabled' => true,
                'installable' => true,
            ]),
            'features' => json_encode([
                'offline' => true,
                'dataSync' => true,
                'multiUser' => true,
            ]),
            'subscription_tiers' => json_encode(['basic', 'pro', 'enterprise']),
            'requires_subscription' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
```

---

## Configuration Template

```php
// config/modules.php
return [
    'core' => [
        'name' => 'MyGrowNet Core',
        'slug' => 'core',
        'category' => 'core',
        'description' => 'Core MLM platform features',
        'icon' => 'home',
        'color' => 'blue',
        'account_types' => ['member'],
        'price' => 0,
        'requires_subscription' => false,
        'routes' => [
            'integrated' => '/dashboard',
        ],
        'pwa' => [
            'enabled' => false,
        ],
    ],
    
    'sme-accounting' => [
        'name' => 'SME Accounting',
        'slug' => 'accounting',
        'category' => 'sme',
        'description' => 'Complete accounting solution for SMEs',
        'icon' => 'calculator',
        'color' => 'purple',
        'account_types' => ['business'],
        'subscription_tiers' => [
            'basic' => [
                'name' => 'Basic',
                'price' => 100,
                'billing_cycle' => 'monthly',
                'user_limit' => 3,
                'storage_limit_mb' => 1000,
            ],
            'pro' => [
                'name' => 'Professional',
                'price' => 200,
                'billing_cycle' => 'monthly',
                'user_limit' => 10,
                'storage_limit_mb' => 5000,
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 500,
                'billing_cycle' => 'monthly',
                'user_limit' => null, // unlimited
                'storage_limit_mb' => 20000,
            ],
        ],
        'routes' => [
            'integrated' => '/modules/accounting',
            'standalone' => '/apps/accounting',
        ],
        'pwa' => [
            'enabled' => true,
            'installable' => true,
            'offline_capable' => true,
        ],
        'features' => [
            'offline' => true,
            'dataSync' => true,
            'notifications' => true,
            'multiUser' => true,
        ],
    ],
];
```

---

## Service Provider Template

```php
// app/Providers/ModuleServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentModuleRepository;
use App\Infrastructure\Persistence\Repositories\EloquentModuleSubscriptionRepository;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            ModuleRepositoryInterface::class,
            EloquentModuleRepository::class
        );
        
        $this->app->bind(
            ModuleSubscriptionRepositoryInterface::class,
            EloquentModuleSubscriptionRepository::class
        );
    }

    public function boot(): void
    {
        // Load module configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/modules.php', 'modules'
        );
    }
}
```

---

## Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Domain/Module/ModuleTest.php

# Run with coverage
php artisan test --coverage

# Create test file
php artisan make:test Domain/Module/ModuleTest --unit
```

---

## Verification Commands

```bash
# Check if migrations ran
php artisan migrate:status

# Check database tables
php artisan db:show

# Inspect specific table
php artisan db:table modules

# Check seeded data
php artisan tinker
>>> DB::table('modules')->count()
>>> DB::table('modules')->get()
```

---

## Troubleshooting

### Migration Issues

```bash
# Reset database
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback --step=1

# Check migration status
php artisan migrate:status
```

### Seeder Issues

```bash
# Clear and reseed
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=ModuleSeeder
```

### Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize
```

---

## Next Steps After Phase 2

### Phase 3: Application Layer

```bash
# Create use cases
# app/Application/UseCases/Module/SubscribeToModuleUseCase.php

# Create DTOs
# app/Application/DTOs/ModuleDTO.php
```

### Phase 4: Presentation Layer

```bash
# Create controllers
php artisan make:controller HomeHubController
php artisan make:controller ModuleSubscriptionController

# Create middleware
php artisan make:middleware CheckModuleAccess

# Create requests
php artisan make:request SubscribeToModuleRequest
```

---

## Useful Laravel Commands

```bash
# List all routes
php artisan route:list

# List all commands
php artisan list

# Generate IDE helper
php artisan ide-helper:generate

# Run queue worker
php artisan queue:work

# Clear logs
php artisan log:clear
```

---

## Git Workflow

```bash
# Create feature branch
git checkout -b feature/module-system-phase-2

# Stage changes
git add app/Infrastructure/
git add database/migrations/
git add config/modules.php

# Commit
git commit -m "feat: implement module system infrastructure layer"

# Push
git push origin feature/module-system-phase-2
```

---

## Documentation Updates

After completing Phase 2, update:

- [ ] `MODULE_DDD_IMPLEMENTATION_STATUS.md` - Mark Phase 2 complete
- [ ] `PHASE_2_COMPLETE.md` - Create completion summary
- [ ] `README.md` - Update project status

---

**Ready to start Phase 2!** 🚀

**Estimated Time:** 2-3 days  
**Team:** Backend developers  
**Goal:** Connect domain logic to da