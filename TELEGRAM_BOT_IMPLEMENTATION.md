# Telegram Bot Notification System - Complete Implementation

**Status:** Ready to Implement  
**Cost:** FREE Forever  
**Date:** November 21, 2025

---

## Overview

Telegram bot for MyGrowNet to send FREE instant notifications:
- OTP codes
- Payment confirmations
- Level upgrades
- Commission notifications
- System alerts

---

## Step 1: Create Telegram Bot (5 minutes)

### Talk to BotFather on Telegram:

1. Open Telegram app
2. Search for `@BotFather`
3. Send: `/newbot`
4. Choose name: `MyGrowNet Bot`
5. Choose username: `mygrownet_bot` (must end with 'bot')
6. **Copy the token** (looks like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)

### Set Bot Commands:
```
/start - Link your account
/status - Check your account status
/balance - View your wallet balance
/help - Get help
```

Send to BotFather:
```
/setcommands
```
Then paste the commands above.

---

## Step 2: Configuration

Add to `.env`:
```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_BOT_USERNAME=mygrownet_bot
```

Publish config:
```bash
php artisan vendor:publish --provider="Telegram\Bot\Laravel\TelegramServiceProvider"
```

---

## Step 3: Database Migration

The migration file has been created. Update it:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('phone');
            $table->boolean('telegram_notifications')->default(false)->after('telegram_chat_id');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_notifications');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_notifications', 'telegram_linked_at']);
        });
    }
};
```

Run migration:
```bash
php artisan migrate
```

---

## Step 4: Create Telegram Service

Create `app/Services/TelegramNotificationService.php`:

```php
<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Send notification to user
     */
    public function sendToUser(User $user, string $message, array $options = []): bool
    {
        if (!$user->telegram_chat_id || !$user->telegram_notifications) {
            return false;
        }

        try {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_chat_id,
                'text' => $message,
                'parse_mode' => $options['parse_mode'] ?? 'Markdown',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram notification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send OTP code
     */
    public function sendOTP(User $user, string $code): bool
    {
        $message = "🔐 *MyGrowNet OTP Code*\n\n";
        $message .= "Your verification code is: `{$code}`\n\n";
        $message .= "This code expires in 10 minutes.\n";
        $message .= "Never share this code with anyone.";

        return $this->sendToUser($user, $message);
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(User $user, float $amount, string $type): bool
    {
        $message = "✅ *Payment Confirmed*\n\n";
        $message .= "Amount: K" . number_format($amount, 2) . "\n";
        $message .= "Type: {$type}\n";
        $message .= "Status: Verified\n\n";
        $message .= "Thank you for your payment!";

        return $this->sendToUser($user, $message);
    }

    /**
     * Send level upgrade notification
     */
    public function sendLevelUpgrade(User $user, string $newLevel): bool
    {
        $message = "🎉 *Congratulations!*\n\n";
        $message .= "You've been promoted to *{$newLevel}*!\n\n";
        $message .= "New benefits unlocked:\n";
        $message .= "• Higher commission rates\n";
        $message .= "• Exclusive resources\n";
        $message .= "• Priority support\n\n";
        $message .= "Keep growing! 🚀";

        return $this->sendToUser($user, $message);
    }

    /**
     * Send commission earned notification
     */
    public function sendCommissionEarned(User $user, float $amount, string $source): bool
    {
        $message = "💰 *Commission Earned!*\n\n";
        $message .= "Amount: K" . number_format($amount, 2) . "\n";
        $message .= "Source: {$source}\n\n";
        $message .= "Check your dashboard for details.";

        return $this->sendToUser($user, $message);
    }

    /**
     * Send withdrawal processed notification
     */
    public function sendWithdrawalProcessed(User $user, float $amount, string $status): bool
    {
        $emoji = $status === 'approved' ? '✅' : '❌';
        $message = "{$emoji} *Withdrawal {$status}*\n\n";
        $message .= "Amount: K" . number_format($amount, 2) . "\n";
        $message .= "Status: " . ucfirst($status) . "\n\n";
        
        if ($status === 'approved') {
            $message .= "Funds will be transferred within 24 hours.";
        } else {
            $message .= "Please contact support for more information.";
        }

        return $this->sendToUser($user, $message);
    }

    /**
     * Link user account to Telegram
     */
    public function linkAccount(string $chatId, string $linkCode): ?User
    {
        $user = User::where('telegram_link_code', $linkCode)->first();

        if (!$user) {
            return null;
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_notifications' => true,
            'telegram_linked_at' => now(),
            'telegram_link_code' => null,
        ]);

        return $user;
    }

    /**
     * Generate link code for user
     */
    public function generateLinkCode(User $user): string
    {
        $code = strtoupper(substr(md5($user->id . time()), 0, 8));
        
        $user->update(['telegram_link_code' => $code]);

        return $code;
    }
}
```

---

## Step 5: Create Telegram Bot Controller

Create `app/Http/Controllers/TelegramBotController.php`:

```php
<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\TelegramNotificationService;
use App\Models\User;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    public function __construct(
        private TelegramNotificationService $telegramService
    ) {}

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        if ($update->getMessage()) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            // Handle /start command
            if (str_starts_with($text, '/start')) {
                $this->handleStart($chatId, $text);
            }
            // Handle /status command
            elseif ($text === '/status') {
                $this->handleStatus($chatId);
            }
            // Handle /balance command
            elseif ($text === '/balance') {
                $this->handleBalance($chatId);
            }
            // Handle /help command
            elseif ($text === '/help') {
                $this->handleHelp($chatId);
            }
            // Handle link code
            elseif (preg_match('/^[A-Z0-9]{8}$/', $text)) {
                $this->handleLinkCode($chatId, $text);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function handleStart($chatId, $text)
    {
        // Check if link code provided: /start LINKCODE
        $parts = explode(' ', $text);
        if (count($parts) === 2) {
            $this->handleLinkCode($chatId, $parts[1]);
            return;
        }

        $message = "👋 *Welcome to MyGrowNet Bot!*\n\n";
        $message .= "To link your account:\n";
        $message .= "1. Go to your MyGrowNet dashboard\n";
        $message .= "2. Navigate to Settings → Notifications\n";
        $message .= "3. Click 'Link Telegram'\n";
        $message .= "4. Send the code here\n\n";
        $message .= "Or use: /help for more commands";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleLinkCode($chatId, $code)
    {
        $user = $this->telegramService->linkAccount($chatId, $code);

        if ($user) {
            $message = "✅ *Account Linked Successfully!*\n\n";
            $message .= "Welcome, {$user->name}!\n\n";
            $message .= "You'll now receive instant notifications for:\n";
            $message .= "• Payment confirmations\n";
            $message .= "• Level upgrades\n";
            $message .= "• Commission earnings\n";
            $message .= "• Withdrawal updates\n\n";
            $message .= "Use /status to check your account anytime.";
        } else {
            $message = "❌ *Invalid Link Code*\n\n";
            $message .= "Please get a new code from your dashboard.";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleStatus($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Account not linked. Use /start to link your account."
            ]);
            return;
        }

        $message = "📊 *Your Account Status*\n\n";
        $message .= "Name: {$user->name}\n";
        $message .= "Level: {$user->professional_level}\n";
        $message .= "Points: " . number_format($user->lifetime_points) . " LP\n";
        $message .= "Network: {$user->network_size} members\n\n";
        $message .= "Dashboard: " . route('dashboard');

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleBalance($chatId)
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Account not linked. Use /start to link your account."
            ]);
            return;
        }

        $message = "💰 *Your Wallet Balance*\n\n";
        $message .= "Available: K" . number_format($user->wallet_balance ?? 0, 2) . "\n";
        $message .= "Pending: K" . number_format($user->pending_balance ?? 0, 2) . "\n\n";
        $message .= "View details: " . route('wallet.index');

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleHelp($chatId)
    {
        $message = "📖 *MyGrowNet Bot Commands*\n\n";
        $message .= "/start - Link your account\n";
        $message .= "/status - Check account status\n";
        $message .= "/balance - View wallet balance\n";
        $message .= "/help - Show this help message\n\n";
        $message .= "Need support? Visit: " . route('support.index');

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}
```

---

## Step 6: Add Routes

Add to `routes/web.php`:

```php
// Telegram Bot Webhook
Route::post('/telegram/webhook', [App\Http\Controllers\TelegramBotController::class, 'webhook']);
```

---

## Step 7: Set Webhook

Run this command to set up the webhook:

```bash
php artisan tinker
```

Then in tinker:
```php
Telegram::setWebhook(['url' => 'https://yourdomain.com/telegram/webhook']);
```

Or create an artisan command to do it easily.

---

## Step 8: Add to User Model

Add to `app/Models/User.php`:

```php
protected $fillable = [
    // ... existing fields
    'telegram_chat_id',
    'telegram_notifications',
    'telegram_linked_at',
    'telegram_link_code',
];

protected $casts = [
    // ... existing casts
    'telegram_linked_at' => 'datetime',
    'telegram_notifications' => 'boolean',
];
```

---

## Step 9: Usage Examples

### Send OTP:
```php
use App\Services\TelegramNotificationService;

$telegram = app(TelegramNotificationService::class);
$telegram->sendOTP($user, '123456');
```

### Send Payment Confirmation:
```php
$telegram->sendPaymentConfirmation($user, 500, 'Subscription Payment');
```

### Send Level Upgrade:
```php
$telegram->sendLevelUpgrade($user, 'Professional');
```

### Send Commission:
```php
$telegram->sendCommissionEarned($user, 50, 'Referral Bonus');
```

---

## Step 10: User Interface (Link Telegram)

Users need a way to link their Telegram account. Add this to user settings page.

---

## Benefits

✅ **Completely FREE** - No cost ever  
✅ **Instant delivery** - Faster than SMS  
✅ **Rich formatting** - Markdown support  
✅ **Two-way communication** - Users can check status  
✅ **Reliable** - 99.9% uptime  
✅ **No limits** - Unlimited messages  

---

## Next Steps

1. Create bot with BotFather
2. Add token to `.env`
3. Run migration
4. Create service and controller files
5. Set webhook
6. Add link button to user dashboard
7. Test with your account

**Estimated Time:** 2-3 hours for complete implementation

This gives you FREE instant notifications while you build revenue for SMS!
