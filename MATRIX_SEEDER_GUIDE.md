# Matrix Seeder Guide

## Quick Start

### Run Matrix Seeder Only
```bash
php artisan db:seed --class=MatrixSeeder
```

### Run All Seeders (includes matrix)
```bash
php artisan db:seed
```

---

## What Gets Created

### Users Created

#### 1. Root Sponsor
- **Email**: `sponsor@mygrownet.com`
- **Password**: `password`
- **Role**: Investor
- **Matrix Level**: 1 (Associate)
- **Purpose**: Top of the matrix tree

#### 2. Level 1 Users (3 users)
- **Emails**: `level1-user1@mygrownet.com` through `level1-user3@mygrownet.com`
- **Password**: `password`
- **Matrix Level**: 1 (Associate)
- **Sponsor**: Root Sponsor
- **Purpose**: Direct referrals of root sponsor

#### 3. Level 2 Users (9 users)
- **Emails**: `level2-user1@mygrownet.com` through `level2-user9@mygrownet.com`
- **Password**: `password`
- **Matrix Level**: 2 (Professional)
- **Sponsors**: Level 1 users (3 under each)
- **Purpose**: Second level of matrix

#### 4. Level 3 Users (15 users)
- **Emails**: `level3-user1@mygrownet.com` through `level3-user15@mygrownet.com`
- **Password**: `password`
- **Matrix Level**: 3 (Senior)
- **Sponsors**: Some Level 2 users
- **Purpose**: Third level of matrix (partial fill)

#### 5. Pending Users (3 users)
- **Emails**: `pending-user1@mygrownet.com` through `pending-user3@mygrownet.com`
- **Password**: `password`
- **Matrix Level**: None (in spillover queue)
- **Purpose**: Test spillover processing

---

## Matrix Structure Created

```
Root Sponsor (Level 1)
├── Level 1 User 1 (Level 1)
│   ├── Level 2 User 1 (Level 2)
│   │   ├── Level 3 User 1 (Level 3)
│   │   ├── Level 3 User 2 (Level 3)
│   │   └── Level 3 User 3 (Level 3)
│   ├── Level 2 User 2 (Level 2)
│   │   ├── Level 3 User 4 (Level 3)
│   │   ├── Level 3 User 5 (Level 3)
│   │   └── Level 3 User 6 (Level 3)
│   └── Level 2 User 3 (Level 2)
│       ├── Level 3 User 7 (Level 3)
│       ├── Level 3 User 8 (Level 3)
│       └── Level 3 User 9 (Level 3)
├── Level 1 User 2 (Level 1)
│   ├── Level 2 User 4 (Level 2)
│   │   ├── Level 3 User 10 (Level 3)
│   │   ├── Level 3 User 11 (Level 3)
│   │   └── Level 3 User 12 (Level 3)
│   ├── Level 2 User 5 (Level 2)
│   │   ├── Level 3 User 13 (Level 3)
│   │   ├── Level 3 User 14 (Level 3)
│   │   └── Level 3 User 15 (Level 3)
│   └── Level 2 User 6 (Level 2)
└── Level 1 User 3 (Level 1)
    ├── Level 2 User 7 (Level 2)
    ├── Level 2 User 8 (Level 2)
    └── Level 2 User 9 (Level 2)

Spillover Queue:
- Pending User 1
- Pending User 2
- Pending User 3
```

---

## Statistics After Seeding

- **Total Users**: 31 (1 root + 3 L1 + 9 L2 + 15 L3 + 3 pending)
- **Matrix Positions**: 28 (excluding pending users)
- **Spillover Queue**: 3 users
- **Level 1 Fill**: 3/3 (100%)
- **Level 2 Fill**: 9/9 (100%)
- **Level 3 Fill**: 15/27 (55.6%)

---

## Testing Admin Actions

### 1. View Matrix Overview
```
Go to: /admin/matrix
Expected: See 28 positions, 7-level distribution table
```

### 2. Process Spillover
```
Go to: /admin/matrix
Find: Spillover Queue section
Action: Select pending users and click "Process Selected"
Expected: Users get placed in matrix
```

### 3. View User Matrix
```
Go to: /admin/matrix
Find: Recent placements table
Action: Click "View Matrix" on any user
Expected: See their network tree
```

### 4. Test Login as Matrix User
```
Email: level1-user1@mygrownet.com
Password: password
Expected: Can log in and see their dashboard
```

---

## Resetting Matrix Data

### Clear Matrix Only
```bash
php artisan tinker
>>> App\Models\MatrixPosition::truncate();
>>> exit
php artisan db:seed --class=MatrixSeeder
```

### Clear All and Reseed
```bash
php artisan migrate:fresh --seed
```

**⚠️ Warning**: This will delete ALL data!

---

## Customizing the Seeder

### Add More Levels
Edit `database/seeders/MatrixSeeder.php`:

```php
// Add Level 4 users
foreach (array_slice($level3Users, 0, 5) as $index => $sponsor) {
    for ($i = 1; $i <= 3; $i++) {
        // Create Level 4 user
    }
}
```

### Change User Count
Modify the loop ranges:
```php
// More Level 1 users (max 3)
for ($i = 1; $i <= 3; $i++) { ... }

// More Level 3 users
foreach (array_slice($level2Users, 0, 9) as $index => $sponsor) { ... }
```

### Add Custom Data
```php
$user = User::create([
    'name' => 'Custom User',
    'email' => 'custom@example.com',
    'total_investment_amount' => 1000,
    'current_professional_level' => 'manager',
    // ... other fields
]);
```

---

## Troubleshooting

### Issue: "User already exists"
**Solution**: The seeder uses `firstOrCreate`, so it won't duplicate users. Safe to run multiple times.

### Issue: "Matrix position already exists"
**Solution**: The seeder checks if position exists before creating. Safe to run multiple times.

### Issue: "Sponsor not found"
**Solution**: Make sure roles are seeded first. Run full `php artisan db:seed`.

### Issue: "No matrix positions created"
**Solution**: Check if MatrixService is working:
```bash
php artisan tinker
>>> $service = app(App\Services\MatrixService::class);
>>> $user = App\Models\User::first();
>>> $service->placeUserInMatrix($user);
```

---

## Verification

### Check Matrix Positions
```bash
php artisan tinker
>>> App\Models\MatrixPosition::count()
# Should return 28

>>> App\Models\MatrixPosition::where('level', 1)->count()
# Should return 4 (root + 3 level 1 users)
```

### Check Spillover Queue
```bash
php artisan tinker
>>> App\Models\User::whereNull('matrix_position')->whereNotNull('referrer_id')->count()
# Should return 3
```

### View Matrix Structure
```bash
php artisan tinker
>>> $root = App\Models\User::where('email', 'sponsor@mygrownet.com')->first();
>>> $root->buildMatrixStructure(3);
```

---

## Next Steps

After seeding:
1. ✅ Log in as admin
2. ✅ Go to Matrix Management
3. ✅ View the 7-level distribution
4. ✅ Process spillover queue
5. ✅ Click "View Matrix" on users
6. ✅ Test reassignment (if needed)

---

**Created**: January 18, 2025  
**Purpose**: Testing and development of matrix management features
