# Module System Testing Guide

**Last Updated:** December 1, 2025  
**Status:** ✅ All Components Ready for Testing

---

## Vue Components Status

All Vue components have been updated with a mobile-first design:

- ✅ `resources/js/Pages/HomeHub/Index.vue` - Standalone mobile-first home hub
- ✅ `resources/js/Pages/Module/Show.vue` - Standalone module detail page
- ✅ `resources/js/Components/HomeHub/ModuleTile.vue` - Reusable module tile

**Design Features:**
- Mobile-first responsive design
- Standalone pages (no layout wrapper needed)
- Icon mapping for different module types
- Smooth animations and transitions
- Clean, modern UI

---

## Quick Start Testing

### 1. Start the Application

```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite (if using)
npm run dev
```

### 2. Access Home Hub

```
URL: http://127.0.0.1:8000/home-hub
```

**Expected Result:**
- ✅ Page loads without errors
- ✅ Module tiles display
- ✅ Icons and colors render
- ✅ Status badges show correctly

---

## Manual Testing Checklist

### Home Hub Page

- [ ] Navigate to `/home-hub`
- [ ] Verify page loads successfully
- [ ] Check that modules are grouped by category
- [ ] Verify module tiles display:
  - [ ] Module name
  - [ ] Description
  - [ ] Icon/color
  - [ ] Status badge (Active, Beta, Coming Soon)
  - [ ] Action button (Open/Subscribe)

### Module Access

- [ ] Click on a module with access
- [ ] Verify redirects to module page
- [ ] Check module header displays correctly
- [ ] Verify access information shows

### Module Without Access

- [ ] Click on a module without access
- [ ] Verify subscription prompt appears
- [ ] Check that access is denied appropriately

---

## Testing with Tinker

### Test Module Repository

```php
php artisan tinker

// Get all modules
$repo = app(\App\Domain\Module\Repositories\ModuleRepositoryInterface::class);
$modules = $repo->findAll();
count($modules);

// Get a specific module
$module = $repo->findById('mygrow-save');
$module->getName()->value();
$module->getConfiguration()->getIcon();
```

### Test Use Cases

```php
// Test GetUserModulesUseCase
$useCase = app(\App\Application\UseCases\Module\GetUserModulesUseCase::class);
$user = \App\Models\User::find(1);
$modules = $useCase->execute($user);
count($modules);

// Check first module
$first = $modules[0];
$first->toArray();
```

### Test DTOs

```php
// Test ModuleDTO
$module = $repo->findById('mygrow-save');
$dto = \App\Application\DTOs\ModuleDTO::fromEntity($module);
$dto->toArray();

// Test ModuleCardDTO
$cardDto = \App\Application\DTOs\ModuleCardDTO::fromEntity($module, true);
$cardDto->toArray();
```

---

## Database Testing

### Check Seeded Data

```sql
-- Check modules table
SELECT id, name, slug, category, status FROM modules;

-- Check module subscriptions
SELECT * FROM module_subscriptions;

-- Check module access logs
SELECT * FROM module_access_logs LIMIT 10;
```

### Via Tinker

```php
// Check modules
\App\Infrastructure\Persistence\Eloquent\ModuleModel::count();
\App\Infrastructure\Persistence\Eloquent\ModuleModel::all()->pluck('name');

// Check subscriptions
\App\Infrastructure\Persistence\Eloquent\ModuleSubscriptionModel::count();
```

---

## Route Testing

### List All Module Routes

```bash
php artisan route:list | grep -E "home-hub|modules|subscriptions"
```

**Expected Routes:**
```
GET     /home-hub                          home-hub.index
POST    /subscriptions                     subscriptions.store
DELETE  /subscriptions/{subscription}      subscriptions.destroy
POST    /subscriptions/{subscription}/upgrade  subscriptions.upgrade
GET     /modules/{moduleId}                modules.show
```

### Test Route Generation

```php
php artisan tinker

route('home-hub.index');
// Expected: http://127.0.0.1:8000/home-hub

route('modules.show', ['moduleId' => 'mygrow-save']);
// Expected: http://127.0.0.1:8000/modules/mygrow-save

route('subscriptions.store');
// Expected: http://127.0.0.1:8000/subscriptions
```

---

## Middleware Testing

### Test CheckModuleAccess

```php
php artisan tinker

$user = \App\Models\User::find(1);
$middleware = app(\App\Presentation\Http\Middleware\CheckModuleAccess::class);

// Create a mock request
$request = new \Illuminate\Http\Request();
$request->setUserResolver(fn() => $user);

// Test middleware logic (simplified)
$useCase = app(\App\Application\UseCases\Module\CheckModuleAccessUseCase::class);
$access = $useCase->execute($user, 'mygrow-save');
$access->hasAccess; // Should be true or false
```

---

## API Testing with cURL

### Get Home Hub (requires authentication)

```bash
# First, get CSRF token and session cookie by logging in through browser
# Then use the session cookie:

curl -X GET http://127.0.0.1:8000/home-hub \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE" \
  -H "Accept: application/json"
```

### Subscribe to Module

```bash
curl -X POST http://127.0.0.1:8000/subscriptions \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN" \
  -d '{
    "module_id": "mygrow-save",
    "tier": "basic",
    "amount": 50.00,
    "currency": "ZMW",
    "billing_cycle": "monthly"
  }'
```

---

## Common Issues & Solutions

### Issue: "Call to undefined method"

**Symptom:** Error when accessing DTOs  
**Solution:** Check that you're accessing value objects correctly:
```php
// ❌ Wrong
$module->getIcon()

// ✅ Correct
$module->getConfiguration()->getIcon()
```

### Issue: "Module not found"

**Symptom:** 404 when accessing module  
**Solution:** Check that modules are seeded:
```bash
php artisan db:seed --class=ModuleSeeder
```

### Issue: "Access denied"

**Symptom:** Redirected from module page  
**Solution:** Check user account type and module requirements:
```php
$user->account_type; // Check user's account type
$module->getAccountTypes(); // Check required account types
```

### Issue: "Subscription not working"

**Symptom:** Can't subscribe to module  
**Solution:** Check validation and use case:
```php
// Test subscription use case
$useCase = app(\App\Application\UseCases\Module\SubscribeToModuleUseCase::class);
$dto = $useCase->execute(1, 'mygrow-save', 'basic', 50.00);
```

---

## Performance Testing

### Check Query Count

```php
// Enable query logging
\DB::enableQueryLog();

// Execute action
$useCase = app(\App\Application\UseCases\Module\GetUserModulesUseCase::class);
$user = \App\Models\User::find(1);
$modules = $useCase->execute($user);

// Check queries
$queries = \DB::getQueryLog();
count($queries); // Should be minimal (N+1 check)
```

---

## Automated Testing

### Run Feature Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Module/HomeHubTest.php

# Run with coverage
php artisan test --coverage
```

### Create New Tests

```bash
# Create feature test
php artisan make:test Module/HomeHubTest

# Create unit test
php artisan make:test Module/ModuleDTOTest --unit
```

---

## Browser Testing Checklist

### Desktop (Chrome/Firefox/Safari)

- [ ] Home Hub loads correctly
- [ ] Module tiles are responsive
- [ ] Hover effects work
- [ ] Click actions work
- [ ] Navigation is smooth

### Mobile (Responsive)

- [ ] Layout adapts to mobile
- [ ] Touch interactions work
- [ ] Text is readable
- [ ] Buttons are tappable
- [ ] No horizontal scroll

### Accessibility

- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] Color contrast is sufficient
- [ ] Focus indicators visible
- [ ] ARIA labels present

---

## Production Readiness Checklist

### Before Deployment

- [ ] All tests passing
- [ ] No console errors
- [ ] Database migrations run
- [ ] Seeders tested
- [ ] Routes registered
- [ ] Middleware working
- [ ] DTOs tested
- [ ] Use cases tested
- [ ] UI/UX reviewed
- [ ] Performance acceptable

### Post-Deployment

- [ ] Monitor error logs
- [ ] Check user feedback
- [ ] Verify analytics
- [ ] Test on production
- [ ] Backup database

---

## Support & Debugging

### Enable Debug Mode

```env
APP_DEBUG=true
APP_ENV=local
```

### Check Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View specific error
grep "ModuleCardDTO" storage/logs/laravel.log
```

### Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

**Happy Testing!** 🧪

