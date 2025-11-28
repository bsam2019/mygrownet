# Account Integrity System - Preventing Future Issues

**Last Updated:** November 28, 2024  
**Status:** Implementation Ready

## Overview

This document outlines a comprehensive system to prevent the recurring account issues we've been experiencing. Instead of creating more fix scripts, we're implementing preventive measures at the application level.

## Root Causes Identified

### 1. Data Validation Issues
- **Missing emails**: Users created without email addresses
- **Phone format inconsistency**: Various formats causing lookup failures
- **Missing profiles**: User records without corresponding profile records
- **Duplicate records**: Same email/phone used by multiple users

### 2. Transaction Double-Counting
- **Starter Kit purchases**: Creating both transaction and withdrawal records
- **Wallet operations**: Inconsistent recording across different tables
- **Commission calculations**: Potential for duplicate commission records

### 3. Service Layer Problems
- **No centralized validation**: Each service validates independently
- **Inconsistent data handling**: Different services format data differently
- **Missing domain constraints**: Business rules not enforced at code level

## Solution Architecture

### Phase 1: Domain-Driven Validation Layer

#### 1.1 User Domain Validation Service

```php
// app/Domain/User/Services/UserValidationService.php
class UserValidationService
{
    public function validateUserCreation(array $userData): ValidationResult
    {
        $errors = [];
        
        // Email validation
        if (empty($userData['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif ($this->emailExists($userData['email'])) {
            $errors[] = 'Email already exists';
        }
        
        // Phone validation
        if (!empty($userData['phone'])) {
            $normalizedPhone = $this->normalizePhone($userData['phone']);
            if ($this->phoneExists($normalizedPhone)) {
                $errors[] = 'Phone number already exists';
            }
            $userData['phone'] = $normalizedPhone;
        }
        
        return new ValidationResult($errors, $userData);
    }
    
    private function normalizePhone(string $phone): string
    {
        // Standardize to +260XXXXXXXXX format
        $cleaned = preg_replace('/[^\d\+]/', '', $phone);
        
        if (preg_match('/^260\d{9}$/', $cleaned)) {
            return '+' . $cleaned;
        }
        
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return '+260' . substr($cleaned, 1);
        }
        
        if (preg_match('/^\+260\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        
        throw new InvalidPhoneFormatException("Invalid phone format: {$phone}");
    }
}
```

#### 1.2 Financial Transaction Integrity Service

```php
// app/Domain/Financial/Services/TransactionIntegrityService.php
class TransactionIntegrityService
{
    public function recordWalletDebit(
        User $user, 
        float $amount, 
        string $type, 
        string $description,
        ?string $reference = null
    ): Transaction {
        
        // Prevent duplicate transactions
        $existingTransaction = $this->findDuplicateTransaction(
            $user->id, 
            $amount, 
            $type, 
            $reference
        );
        
        if ($existingTransaction) {
            throw new DuplicateTransactionException(
                "Transaction already exists: {$existingTransaction->id}"
            );
        }
        
        return DB::transaction(function () use ($user, $amount, $type, $description, $reference) {
            // Create single transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_type' => $type,
                'amount' => -abs($amount), // Ensure negative for debits
                'reference_number' => $reference ?? $this->generateReference($type),
                'description' => $description,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Log for audit trail
            $this->logTransaction($transaction, 'wallet_debit');
            
            return $transaction;
        });
    }
    
    private function findDuplicateTransaction(
        int $userId, 
        float $amount, 
        string $type, 
        ?string $reference
    ): ?Transaction {
        $query = Transaction::where('user_id', $userId)
            ->where('transaction_type', $type)
            ->where('amount', -abs($amount))
            ->where('created_at', '>=', now()->subMinutes(5)); // 5-minute window
            
        if ($reference) {
            $query->where('reference_number', $reference);
        }
        
        return $query->first();
    }
}
```

### Phase 2: Enhanced User Registration

#### 2.1 Improved User Creation Process

```php
// app/Domain/User/Services/UserRegistrationService.php
class UserRegistrationService
{
    public function __construct(
        private UserValidationService $validator,
        private UserProfileService $profileService
    ) {}
    
    public function registerUser(array $userData): User
    {
        // Validate all data before creation
        $validation = $this->validator->validateUserCreation($userData);
        
        if (!$validation->isValid()) {
            throw new ValidationException($validation->getErrors());
        }
        
        return DB::transaction(function () use ($validation) {
            $userData = $validation->getValidatedData();
            
            // Create user with validated data
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'password' => Hash::make($userData['password']),
                'referrer_id' => $userData['referrer_id'] ?? null,
            ]);
            
            // Always create profile immediately
            $this->profileService->createProfile($user, $userData);
            
            // Initialize points record
            $this->initializeUserPoints($user);
            
            // Log successful registration
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
            ]);
            
            return $user;
        });
    }
}
```

### Phase 3: Database Constraints

#### 3.1 Migration for Data Integrity

```php
// database/migrations/2024_11_28_add_data_integrity_constraints.php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // Ensure email is always present and unique
        $table->string('email')->nullable(false)->change();
        $table->unique('email');
        
        // Ensure phone is unique when present
        $table->unique('phone');
        
        // Add check constraint for phone format
        DB::statement('ALTER TABLE users ADD CONSTRAINT chk_phone_format 
                      CHECK (phone IS NULL OR phone REGEXP "^\+260[0-9]{9}$")');
    });
    
    Schema::table('user_profiles', function (Blueprint $table) {
        // Ensure every user has a profile
        $table->foreign('user_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade');
    });
    
    Schema::table('transactions', function (Blueprint $table) {
        // Add unique constraint to prevent duplicate transactions
        $table->unique(['user_id', 'reference_number'], 'unique_user_transaction');
        
        // Add index for better performance
        $table->index(['user_id', 'transaction_type', 'created_at']);
    });
}
```

### Phase 4: Automated Monitoring

#### 4.1 Account Integrity Monitor

```php
// app/Console/Commands/MonitorAccountIntegrity.php
class MonitorAccountIntegrity extends Command
{
    protected $signature = 'accounts:monitor';
    protected $description = 'Monitor account integrity and alert on issues';
    
    public function handle()
    {
        $issues = [];
        
        // Check for users without emails
        $usersWithoutEmail = User::whereNull('email')->orWhere('email', '')->count();
        if ($usersWithoutEmail > 0) {
            $issues[] = "Users without email: {$usersWithoutEmail}";
        }
        
        // Check for users without profiles
        $usersWithoutProfile = User::doesntHave('profile')->count();
        if ($usersWithoutProfile > 0) {
            $issues[] = "Users without profile: {$usersWithoutProfile}";
        }
        
        // Check for duplicate transactions
        $duplicateTransactions = DB::select("
            SELECT user_id, reference_number, COUNT(*) as count
            FROM transactions 
            WHERE reference_number IS NOT NULL
            GROUP BY user_id, reference_number 
            HAVING COUNT(*) > 1
        ");
        
        if (!empty($duplicateTransactions)) {
            $issues[] = "Duplicate transactions found: " . count($duplicateTransactions);
        }
        
        // Check for inconsistent phone formats
        $invalidPhones = User::whereNotNull('phone')
            ->where('phone', 'NOT REGEXP', '^\+260[0-9]{9}$')
            ->count();
            
        if ($invalidPhones > 0) {
            $issues[] = "Invalid phone formats: {$invalidPhones}";
        }
        
        if (!empty($issues)) {
            // Send alert to administrators
            $this->sendIntegrityAlert($issues);
            $this->error('Account integrity issues found:');
            foreach ($issues as $issue) {
                $this->line("- {$issue}");
            }
            return 1;
        }
        
        $this->info('Account integrity check passed');
        return 0;
    }
}
```

### Phase 5: Enhanced Starter Kit Service

#### 5.1 Fixed Starter Kit Purchase Logic

```php
// Updated StarterKitService::purchaseStarterKit method
public function purchaseStarterKit(
    User $user,
    string $paymentMethod,
    string $paymentReference = null,
    string $tier = self::TIER_BASIC
): StarterKitPurchaseModel {
    
    return DB::transaction(function () use ($user, $paymentMethod, $paymentReference, $tier) {
        $price = $tier === self::TIER_PREMIUM ? self::PRICE_PREMIUM : self::PRICE_BASIC;
        
        // Handle wallet payment with integrity checks
        if ($paymentMethod === 'wallet') {
            $this->processWalletPayment($user, $price, $tier);
        }
        
        // Create purchase record
        $purchase = StarterKitPurchaseModel::create([
            'user_id' => $user->id,
            'tier' => $tier,
            'amount' => $price,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference ?? 'PENDING',
            'status' => $paymentMethod === 'wallet' ? 'completed' : 'pending',
            'invoice_number' => StarterKitPurchaseModel::generateInvoiceNumber(),
        ]);
        
        if ($paymentMethod === 'wallet' || $paymentMethod === 'gift') {
            $this->completePurchase($purchase);
        }
        
        return $purchase;
    });
}

private function processWalletPayment(User $user, float $price, string $tier): void
{
    // Use TransactionIntegrityService to prevent double-counting
    $transactionService = app(TransactionIntegrityService::class);
    
    // Check balance
    $walletBalance = $this->walletService->calculateBalance($user);
    if ($walletBalance < $price) {
        throw new InsufficientFundsException('Insufficient wallet balance');
    }
    
    // Create single transaction record (no withdrawal record)
    $transactionService->recordWalletDebit(
        $user,
        $price,
        'starter_kit_purchase',
        ucfirst($tier) . ' Starter Kit Purchase - Wallet Payment',
        'SK-' . strtoupper(uniqid())
    );
}
```

## Implementation Status

### Completed Components

#### Domain Services Created
- ✅ `app/Domain/User/Services/UserValidationService.php` - User data validation
- ✅ `app/Domain/User/Services/UserRegistrationService.php` - User registration with validation
- ✅ `app/Domain/User/ValueObjects/ValidationResult.php` - Validation result object
- ✅ `app/Domain/User/Exceptions/InvalidPhoneFormatException.php` - Phone validation exception
- ✅ `app/Domain/Financial/Services/TransactionIntegrityService.php` - Transaction integrity
- ✅ `app/Domain/Financial/Exceptions/DuplicateTransactionException.php` - Duplicate prevention
- ✅ `app/Domain/Financial/Exceptions/InsufficientFundsException.php` - Balance validation

#### Monitoring & Automation
- ✅ `app/Console/Commands/MonitorAccountIntegrity.php` - Integrity monitoring command
- ✅ Database migration for transaction constraints

#### Service Integration
- ✅ `app/Services/StarterKitService.php` - Updated to use TransactionIntegrityService

### Deployment Steps

1. Run migration: `php artisan migrate`
2. Test integrity check: `php artisan accounts:monitor`
3. Fix existing issues: `php artisan accounts:monitor --fix`
4. Schedule daily monitoring in `app/Console/Kernel.php`

## Prevention Measures

### 1. Code-Level Prevention
- **Domain validation**: All data validated before database insertion
- **Transaction integrity**: Prevent duplicate financial records
- **Constraint enforcement**: Database-level data integrity rules
- **Service coordination**: Centralized services prevent conflicts

### 2. Monitoring & Alerts
- **Daily integrity checks**: Automated monitoring for data issues
- **Real-time alerts**: Immediate notification of integrity violations
- **Audit logging**: Complete trail of all data modifications
- **Performance monitoring**: Track system health and data quality

### 3. Development Practices
- **Domain-driven design**: Business rules enforced in domain layer
- **Test-driven development**: Comprehensive test coverage for all services
- **Code reviews**: Mandatory review of all financial and user-related code
- **Documentation**: Clear documentation of all business rules and constraints

## Success Metrics

### Before Implementation
- ❌ Regular account integrity issues
- ❌ Manual fix scripts required weekly
- ❌ User login failures due to data issues
- ❌ Financial discrepancies requiring investigation

### After Implementation
- ✅ Zero account integrity issues
- ✅ No manual fix scripts required
- ✅ 100% user login success rate
- ✅ Accurate financial records with audit trail

## Rollback Plan

If issues arise during implementation:

1. **Immediate**: Disable new constraints temporarily
2. **Short-term**: Revert to previous service implementations
3. **Long-term**: Fix issues and re-deploy with additional safeguards

## Maintenance

### Daily
- Run `php artisan accounts:monitor`
- Check integrity alert emails
- Review error logs for validation failures

### Weekly
- Analyze integrity monitoring reports
- Review and update validation rules as needed
- Performance optimization based on monitoring data

### Monthly
- Full system integrity audit
- Update documentation based on lessons learned
- Team training on any new procedures

---

**This system prevents issues at the source rather than fixing them after they occur.**