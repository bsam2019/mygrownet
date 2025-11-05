# Idempotency Protection System

**Date:** 2025-11-05  
**Status:** ✅ Implemented  
**Purpose:** Prevent duplicate operations when network issues cause multiple submissions

## Problem

When users experience network problems (slow connection, timeouts, etc.), they may:
1. Click submit multiple times thinking the first click didn't work
2. Have the browser retry the request automatically
3. Experience race conditions where multiple requests are processed simultaneously

This can result in:
- ❌ Duplicate LGR transfers
- ❌ Multiple withdrawals for the same request
- ❌ Duplicate transactions in the database
- ❌ Incorrect balance calculations

## Solution

We've implemented a **three-layer idempotency protection system**:

### Layer 1: Database Constraints
**Purpose:** Last line of defense at the database level

**Implementation:**
- `reference_number` column in `transactions` table has `UNIQUE` constraint
- Prevents duplicate transactions even if all other checks fail
- Database will reject duplicate reference numbers with an error

**Files:**
- `database/migrations/2024_02_20_000000_create_transactions_table.php`

### Layer 2: Backend Idempotency Service
**Purpose:** Prevent duplicate processing of the same operation

**Implementation:**
```php
// Generate unique key for this operation
$idempotencyKey = $this->idempotencyService->generateKey(
    $user->id,
    'lgr_transfer',
    ['amount' => $amount, 'timestamp' => $timestamp]
);

// Execute with idempotency protection
$result = $this->idempotencyService->execute(
    $idempotencyKey,
    function () use ($user, $amount, $feePercentage) {
        return $this->executeTransfer($user, $amount, $feePercentage);
    },
    lockDuration: 30, // 30 seconds lock
    keyTtl: 300 // Remember for 5 minutes
);
```

**Features:**
- **Cache-based locking:** Only one request can process at a time
- **Result caching:** Subsequent requests get the cached result
- **Automatic cleanup:** Keys expire after TTL
- **Logging:** All duplicate attempts are logged

**Files:**
- `app/Services/IdempotencyService.php` (new service)
- `app/Http/Controllers/MyGrowNet/LgrTransferController.php` (updated)

### Layer 3: Frontend Debouncing
**Purpose:** Prevent users from submitting multiple times

**Implementation:**
```typescript
// Prevent double submission
if (transferProcessing.value) {
    showNotification('Transfer is already in progress. Please wait.', 'warning');
    return;
}

// Prevent rapid submissions (minimum 3 seconds between transfers)
const now = Date.now();
if (now - lastTransferTime < 3000) {
    showNotification('Please wait a moment before submitting another transfer.', 'warning');
    return;
}
lastTransferTime = now;
```

**Features:**
- **Processing state:** Button disabled while processing
- **Time-based throttling:** Minimum 3 seconds between submissions
- **Visual feedback:** "Processing..." text on button
- **User notifications:** Clear messages about what's happening

**Files:**
- `resources/js/pages/MyGrowNet/Wallet.vue` (updated)

## How It Works

### Normal Flow (No Network Issues)
```
1. User clicks "Transfer" button
2. Frontend: Check if already processing → No
3. Frontend: Check time since last transfer → OK
4. Frontend: Disable button, show "Processing..."
5. Backend: Generate idempotency key
6. Backend: Check if already completed → No
7. Backend: Acquire lock
8. Backend: Execute transfer
9. Backend: Cache result
10. Backend: Release lock
11. Frontend: Show success message
12. Frontend: Re-enable button after 2 seconds
```

### With Network Issues (Duplicate Attempt)
```
1. User clicks "Transfer" button
2. Request sent but network is slow
3. User clicks "Transfer" again (thinking it didn't work)
4. Frontend: Check if already processing → YES
5. Frontend: Show warning "Transfer is already in progress"
6. Frontend: Prevent second request
```

### With Multiple Rapid Clicks
```
1. User clicks "Transfer" button rapidly 3 times
2. First click: Processes normally
3. Second click (0.5s later): Blocked by time throttle
4. Third click (1s later): Blocked by time throttle
5. Frontend: Show warning "Please wait a moment"
```

### With Concurrent Requests (Race Condition)
```
1. Two requests arrive at backend simultaneously
2. Both generate same idempotency key
3. Request A: Acquires lock → Processes
4. Request B: Tries to acquire lock → Waits
5. Request A: Completes and caches result
6. Request B: Gets cached result instead of processing again
```

## Configuration

### Idempotency Service Settings

```php
// Lock duration (how long to hold the lock)
lockDuration: 30 // 30 seconds

// Key TTL (how long to remember this operation)
keyTtl: 300 // 5 minutes (300 seconds)
```

### Frontend Throttling

```typescript
// Minimum time between submissions
const THROTTLE_DURATION = 3000; // 3 seconds

// Cooldown after successful transfer
const COOLDOWN_DURATION = 2000; // 2 seconds
```

## Idempotency Key Generation

The idempotency key is generated based on:
1. **User ID:** Ensures keys are user-specific
2. **Operation type:** Different operations have different keys
3. **Parameters:** Amount and timestamp (rounded to minute)

```php
// Example key generation
$timestamp = floor(time() / 60) * 60; // Round to nearest minute
$idempotencyKey = $this->idempotencyService->generateKey(
    $user->id,
    'lgr_transfer',
    ['amount' => $amount, 'timestamp' => $timestamp]
);

// Result: "lgr_transfer:35:a1b2c3d4e5f6"
```

**Why round timestamp to minute?**
- Allows retries within the same minute to be treated as duplicates
- Prevents legitimate transfers in different minutes from being blocked
- Balances between protection and usability

## Monitoring

### Logs to Watch

**Duplicate Detection:**
```
[WARNING] Duplicate LGR transfer attempt detected
{
    "user_id": 35,
    "amount": 100,
    "idempotency_key": "lgr_transfer:35:a1b2c3d4"
}
```

**Transfer In Progress:**
```
[WARNING] LGR transfer already in progress
{
    "user_id": 35,
    "amount": 100,
    "idempotency_key": "lgr_transfer:35:a1b2c3d4"
}
```

**Idempotency Service:**
```
[INFO] Idempotency: Returning cached result
{
    "key": "lgr_transfer:35:a1b2c3d4"
}
```

### Metrics to Track

1. **Duplicate attempt rate:** How often are duplicates detected?
2. **Lock contention:** How often do requests wait for locks?
3. **Cache hit rate:** How often are cached results returned?

## Testing

### Test Duplicate Prevention

```php
// Simulate rapid duplicate requests
$user = User::find(35);
$amount = 100;

// First request
$response1 = $this->actingAs($user)
    ->post(route('mygrownet.wallet.lgr-transfer'), ['amount' => $amount]);

// Immediate second request (should be blocked)
$response2 = $this->actingAs($user)
    ->post(route('mygrownet.wallet.lgr-transfer'), ['amount' => $amount]);

// Verify only one transaction was created
$this->assertEquals(1, Transaction::where('user_id', $user->id)
    ->where('transaction_type', 'lgr_transfer_out')
    ->where('amount', -$amount)
    ->count());
```

### Test Frontend Throttling

1. Open browser console
2. Click "Transfer" button rapidly
3. Verify warning messages appear
4. Verify only one request is sent

### Test Network Timeout Scenario

1. Use browser dev tools to throttle network to "Slow 3G"
2. Click "Transfer" button
3. Wait 2 seconds
4. Click "Transfer" button again
5. Verify second click is blocked
6. Verify only one transfer is processed

## Extending to Other Operations

The idempotency service can be used for any operation that needs duplicate protection:

### Example: Withdrawal Requests

```php
public function store(Request $request)
{
    $user = auth()->user();
    $amount = $request->input('amount');
    
    // Generate idempotency key
    $timestamp = floor(time() / 60) * 60;
    $idempotencyKey = $this->idempotencyService->generateKey(
        $user->id,
        'withdrawal_request',
        ['amount' => $amount, 'timestamp' => $timestamp]
    );
    
    // Execute with protection
    $result = $this->idempotencyService->execute(
        $idempotencyKey,
        function () use ($user, $amount) {
            return $this->createWithdrawal($user, $amount);
        }
    );
    
    return redirect()->back()->with('success', $result['message']);
}
```

### Example: Starter Kit Purchase

```php
public function storePurchase(Request $request)
{
    $user = auth()->user();
    $tier = $request->input('tier');
    
    // Generate idempotency key
    $idempotencyKey = $this->idempotencyService->generateKey(
        $user->id,
        'starter_kit_purchase',
        ['tier' => $tier]
    );
    
    // Execute with protection
    $result = $this->idempotencyService->execute(
        $idempotencyKey,
        function () use ($user, $tier) {
            return $this->purchaseUseCase->execute($user, 'wallet', null, $tier);
        },
        lockDuration: 60, // Longer lock for purchases
        keyTtl: 3600 // Remember for 1 hour
    );
    
    return redirect($result['redirect'])->with('success', $result['message']);
}
```

## Troubleshooting

### Issue: "Operation already in progress" but nothing is processing

**Cause:** Lock wasn't released properly (server crash, etc.)

**Solution:**
```php
// Clear the idempotency key
$idempotencyService = app(\App\Services\IdempotencyService::class);
$key = $idempotencyService->generateKey($userId, 'lgr_transfer', $params);
$idempotencyService->clear($key);
```

### Issue: Legitimate transfers being blocked

**Cause:** Timestamp rounding is too coarse

**Solution:** Adjust the timestamp rounding or key TTL:
```php
// More granular: round to 30 seconds instead of 60
$timestamp = floor(time() / 30) * 30;
```

### Issue: Cache filling up with idempotency keys

**Cause:** TTL is too long

**Solution:** Reduce the key TTL:
```php
keyTtl: 180 // 3 minutes instead of 5
```

## Benefits

✅ **Prevents duplicate transactions** - No more double charges  
✅ **Better user experience** - Clear feedback about what's happening  
✅ **Reduced support burden** - Fewer "I was charged twice" complaints  
✅ **Data integrity** - Consistent database state  
✅ **Audit trail** - All duplicate attempts are logged  
✅ **Reusable** - Can be applied to any operation  

## Related Files

- `app/Services/IdempotencyService.php` - Core idempotency service
- `app/Http/Controllers/MyGrowNet/LgrTransferController.php` - LGR transfer with idempotency
- `resources/js/pages/MyGrowNet/Wallet.vue` - Frontend debouncing
- `database/migrations/2024_02_20_000000_create_transactions_table.php` - Unique constraint

---

**Implemented By:** Kiro AI Assistant  
**Date:** 2025-11-05  
**Status:** ✅ Ready for Production
