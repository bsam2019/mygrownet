# Telegram Bot - Usage Examples

**How to send notifications from your code**

---

## Quick Start - Using the Trait

### Step 1: Add Trait to Your Controller

```php
use App\Traits\SendsTelegramNotifications;

class PaymentController extends Controller
{
    use SendsTelegramNotifications;
    
    public function verifyPayment($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        $user = $payment->user;
        
        // Verify payment logic...
        $payment->update(['status' => 'verified']);
        
        // Send Telegram notification
        $this->notifyViaTelegram($user, 'payment', $payment->amount, 'Subscription');
        
        return response()->json(['success' => true]);
    }
}
```

### Step 2: Use in Different Scenarios

```php
// OTP Code
$this->notifyViaTelegram($user, 'otp', '123456');

// Payment Confirmation
$this->notifyViaTelegram($user, 'payment', 500, 'Subscription Payment');

// Level Upgrade
$this->notifyViaTelegram($user, 'level_upgrade', 'Professional');

// Commission Earned
$this->notifyViaTelegram($user, 'commission', 50, 'Referral Bonus');

// Withdrawal Processed
$this->notifyViaTelegram($user, 'withdrawal', 1000, 'approved');
```

---

## Advanced Usage - Direct Use Case

### Custom Notifications

```php
use App\Domain\Notification\Services\TelegramNotificationService;
use App\Domain\Notification\ValueObjects\TelegramChatId;

class CustomNotificationService
{
    public function __construct(
        private TelegramNotificationService $telegram
    ) {}
    
    public function sendCustomAlert(User $user, string $title, string $message)
    {
        if (!$user->telegram_chat_id) {
            return false;
        }
        
        $chatId = TelegramChatId::fromString($user->telegram_chat_id);
        
        $fullMessage = "🔔 *{$title}*\n\n{$message}";
        
        return $this->telegram->sendMessage(
            $chatId, 
            $fullMessage, 
            ['parse_mode' => 'Markdown']
        );
    }
}
```

---

## Real Integration Examples

### 1. Payment Verification

**File:** `app/Http/Controllers/PaymentController.php`

```php
use App\Traits\SendsTelegramNotifications;

class PaymentController extends Controller
{
    use SendsTelegramNotifications;
    
    public function verifyMobileMoneyPayment(Request $request)
    {
        $payment = MemberPayment::where('transaction_id', $request->transaction_id)->first();
        
        if ($payment && $payment->status === 'pending') {
            $payment->update(['status' => 'verified']);
            
            // Send Telegram notification
            $this->notifyViaTelegram(
                $payment->user, 
                'payment', 
                $payment->amount, 
                'Mobile Money Payment'
            );
            
            return response()->json(['success' => true]);
        }
    }
}
```

### 2. Level Upgrade

**File:** `app/Services/LevelService.php`

```php
use App\Traits\SendsTelegramNotifications;

class LevelService
{
    use SendsTelegramNotifications;
    
    public function checkAndUpgradeLevel(User $user)
    {
        $newLevel = $this->calculateLevel($user);
        
        if ($newLevel !== $user->professional_level) {
            $user->update(['professional_level' => $newLevel]);
            
            // Send Telegram notification
            $this->notifyViaTelegram($user, 'level_upgrade', $newLevel);
            
            return true;
        }
        
        return false;
    }
}
```

### 3. Commission Award

**File:** `app/Services/CommissionService.php`

```php
use App\Traits\SendsTelegramNotifications;

class CommissionService
{
    use SendsTelegramNotifications;
    
    public function awardReferralCommission(User $referrer, User $newMember, float $amount)
    {
        // Award commission logic
        $referrer->increment('wallet_balance', $amount);
        
        // Record transaction
        Transaction::create([
            'user_id' => $referrer->id,
            'type' => 'commission',
            'amount' => $amount,
            'description' => "Referral commission from {$newMember->name}"
        ]);
        
        // Send Telegram notification
        $this->notifyViaTelegram(
            $referrer, 
            'commission', 
            $amount, 
            'Referral Bonus'
        );
    }
}
```

### 4. OTP Generation

**File:** `app/Services/OTPService.php`

```php
use App\Traits\SendsTelegramNotifications;

class OTPService
{
    use SendsTelegramNotifications;
    
    public function generateAndSend(User $user)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP
        cache()->put("otp:{$user->id}", $code, now()->addMinutes(10));
        
        // Send via SMS (if configured)
        // $this->sendSMS($user, $code);
        
        // Send via Telegram (FREE!)
        $this->notifyViaTelegram($user, 'otp', $code);
        
        return $code;
    }
}
```

### 5. Withdrawal Processing

**File:** `app/Http/Controllers/Admin/WithdrawalController.php`

```php
use App\Traits\SendsTelegramNotifications;

class WithdrawalController extends Controller
{
    use SendsTelegramNotifications;
    
    public function approve($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        
        $withdrawal->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);
        
        // Send Telegram notification
        $this->notifyViaTelegram(
            $withdrawal->user, 
            'withdrawal', 
            $withdrawal->amount, 
            'approved'
        );
        
        return back()->with('success', 'Withdrawal approved');
    }
    
    public function reject($id, Request $request)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        
        $withdrawal->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);
        
        // Send Telegram notification
        $this->notifyViaTelegram(
            $withdrawal->user, 
            'withdrawal', 
            $withdrawal->amount, 
            'rejected'
        );
        
        return back()->with('success', 'Withdrawal rejected');
    }
}
```

---

## Broadcast to Multiple Users

```php
use App\Application\UseCases\Notification\SendTelegramNotificationUseCase;

class BroadcastService
{
    public function sendToAllLinkedUsers(string $message)
    {
        $users = User::whereNotNull('telegram_chat_id')
            ->where('telegram_notifications', true)
            ->get();
        
        $telegram = app(SendTelegramNotificationUseCase::class);
        
        foreach ($users as $user) {
            try {
                $chatId = TelegramChatId::fromString($user->telegram_chat_id);
                $telegram->sendMessage($chatId, $message);
            } catch (\Exception $e) {
                \Log::error("Failed to send to user {$user->id}");
            }
        }
    }
}
```

---

## Testing

```php
// In tinker or test
php artisan tinker

$user = User::first();
$telegram = app(\App\Application\UseCases\Notification\SendTelegramNotificationUseCase::class);

// Test OTP
$telegram->sendOTP($user, '123456');

// Test payment
$telegram->sendPaymentConfirmation($user, 500, 'Test Payment');

// Test level upgrade
$telegram->sendLevelUpgrade($user, 'Professional');
```

---

## Best Practices

1. **Always wrap in try-catch** - Don't let Telegram failures break your main logic
2. **Check if linked** - The use case already does this, but be aware
3. **Use the trait** - Simplifies integration across controllers
4. **Log failures** - Track notification issues for debugging
5. **Test first** - Use tinker to test before integrating

---

## Summary

**To send Telegram notifications:**

1. Add `use SendsTelegramNotifications;` to your controller/service
2. Call `$this->notifyViaTelegram($user, 'type', ...params)`
3. Done! Users with linked Telegram get instant FREE notifications

**No SMS costs, instant delivery, unlimited messages!**
