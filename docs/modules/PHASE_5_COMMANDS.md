# Phase 5: Configuration & Integration - Command Reference

**Last Updated:** December 1, 2025  
**Status:** In Progress

---

## Overview

Phase 5 focuses on completing the configuration, seeding comprehensive module data, and integrating the module system with the existing platform.

---

## Checklist

### Configuration
- [x] Basic config structure (`config/modules.php`)
- [ ] Complete module definitions
- [ ] Subscription tier pricing
- [ ] PWA settings
- [ ] Feature flags

### Database Seeding
- [x] Basic seeder structure (`ModuleSeeder.php`)
- [ ] Seed all modules (MyGrow Save, Wedding Planner, etc.)
- [ ] Seed sample subscriptions for testing
- [ ] Seed module access logs

### Integration
- [ ] Add Home Hub to main navigation
- [ ] Create subscription modal component
- [ ] Integrate with payment system
- [ ] Add module navigation components
- [ ] Create admin module management

### Testing
- [ ] Run migrations
- [ ] Run seeders
- [ ] Test Home Hub page
- [ ] Test subscription flow
- [ ] Test access control

---

## Commands

### Run Migrations

```bash
# Run all migrations
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/2025_12_01_122419_create_module_subscriptions_table.php
```

### Run Seeders

```bash
# Run module seeder
php artisan db:seed --class=ModuleSeeder

# Run all seeders
php artisan db:seed
```

### Clear Caches

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
```

### Test Routes

```bash
# List module routes
php artisan route:list | grep -E "home-hub|modules|subscriptions"
```

---

## Module Definitions to Add

### MyGrow Save (Digital Wallet)
- Category: personal
- Free tier + Premium tier
- PWA enabled

### Wedding Planner
- Category: personal
- Subscription-based
- PWA enabled

### Inventory Management
- Category: sme
- Subscription-based
- Multi-user support

### CRM (Customer Relationship Management)
- Category: sme
- Subscription-based
- Multi-user support

### Learning Hub
- Category: core
- Free for all members
- Educational content

### Venture Builder
- Category: enterprise
- Subscription-based
- Investment tracking

---

## Integration Points

### Main Navigation
Add Home Hub link to sidebar navigation.

### Dashboard
Add module quick access cards to dashboard.

### User Profile
Add subscription management to user settings.

### Admin Panel
Add module management to admin dashboard.

---

## Testing Checklist

### Database
- [ ] Modules table has data
- [ ] Module subscriptions table exists
- [ ] Module access logs table exists

### Routes
- [ ] /home-hub accessible
- [ ] /modules/{id} accessible
- [ ] /subscriptions routes work

### UI
- [ ] Home Hub page renders
- [ ] Module tiles display correctly
- [ ] Status badges show correctly
- [ ] Click actions work

### Access Control
- [ ] Middleware protects routes
- [ ] Account type checks work
- [ ] Subscription checks work

---

## Estimated Time

- Configuration: 1 hour
- Seeding: 30 minutes
- Integration: 2-3 hours
- Testing: 1 hour

**Total: 4-5 hours**

