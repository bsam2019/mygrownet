# Gift Starter Kit - Quick Reference

## 🚀 Quick Start

### Enable/Disable Feature
```php
use App\Infrastructure\Persistence\Eloquent\Settings\GiftSettingsModel;

// Disable
GiftSettingsModel::first()->update(['gift_feature_enabled' => false]);

// Enable
GiftSettingsModel::first()->update(['gift_feature_enabled' => true]);
```

### Update Limits
```php
GiftSettingsModel::first()->update([
    'max_gifts_per_month' => 10,           // Increase to 10 gifts/month
    'max_gift_amount_per_month' => 10000,  // Increase to K10,000/month
    'min_wallet_balance_to_gift' => 500,   // Lower minimum to K500
    'gift_fee_percentage' => 5,            // Add 5% fee
]);
```

## 📊 Database Queries

### View All Gifts
```sql
SELECT 
    g.id,
    gifter.name as gifter_name,
    recipient.name as recipient_name,
    g.tier,
    g.amount,
    g.status,
    g.created_at
FROM starter_kit_gifts g
JOIN users gifter ON g.gifter_id = gifter.id
JOIN users recipient ON g.recipient_id = recipient.id
ORDER BY g.created_at DESC;
```

### Top Gifters This Month
```sql
SELECT 
    u.name,
    COUNT(*) as total_gifts,
    SUM(g.amount) as total_amount
FROM starter_kit_gifts g
JOIN users u ON g.gifter_id = u.id
WHERE g.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
GROUP BY u.id, u.name
ORDER BY total_gifts DESC
LIMIT 10;
```

### Gifts by Status
```sql
SELECT 
    status,
    COUNT(*) as count,
    SUM(amount) as total_amount
FROM starter_kit_gifts
GROUP BY status;
```

### Members Who Received Gifts
```sql
SELECT 
    u.name,
    u.phone,
    g.tier,
    gifter.name as gifted_by,
    g.created_at
FROM starter_kit_gifts g
JOIN users u ON g.recipient_id = u.id
JOIN users gifter ON g.gifter_id = gifter.id
WHERE g.status = 'completed'
ORDER BY g.created_at DESC;
```

## 🔧 Common Tasks

### Check User's Gift Limits
```php
$useCase = app(\App\Application\StarterKit\UseCases\GiftStarterKitUseCase::class);
$limits = $useCase->getGiftLimits($userId);

// Returns:
// - remaining_gifts
// - remaining_amount
// - current_wallet_balance
// - etc.
```

### Manually Gift Starter Kit
```php
$useCase = app(\App\Application\StarterKit\UseCases\GiftStarterKitUseCase::class);

try {
    $result = $useCase->execute(
        gifterId: 1,
        recipientId: 2,
        tier: 'basic' // or 'premium'
    );
    
    echo "Success: " . $result['message'];
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Check if User Can Gift
```php
$giftService = app(\App\Domain\StarterKit\Services\GiftService::class);
$gifter = User::find(1);
$recipient = User::find(2);
$amount = 500;

$settingsArray = \App\Infrastructure\Persistence\Eloquent\Settings\GiftSettingsModel::get();
$limits = \App\Domain\StarterKit\ValueObjects\GiftLimits::fromSettings($settingsArray);

$canGift = $giftService->canGiftStarterKit($gifter, $recipient, $amount, $limits);

if (!$canGift) {
    $reason = $giftService->getGiftDenialReason($gifter, $recipient, $amount, $limits);
    echo "Cannot gift: $reason";
}
```

### Get User's Gift History
```php
$gifts = User::find(1)->giftsGiven()
    ->with('recipient')
    ->latest()
    ->get();

foreach ($gifts as $gift) {
    echo "{$gift->recipient->name} - {$gift->tier} - K{$gift->amount}\n";
}
```

## 🐛 Troubleshooting

### Gift Button Not Showing
```php
// Check if member has starter kit
$member = User::find($memberId);
$hasKit = $member->starterKitPurchases()->exists();
echo "Has starter kit: " . ($hasKit ? 'Yes' : 'No');

// Check if member is in downline
$isDownline = \App\Models\UserNetwork::where('referrer_id', $gifterId)
    ->where('user_id', $memberId)
    ->exists();
echo "Is in downline: " . ($isDownline ? 'Yes' : 'No');
```

### Gift Fails Silently
```php
// Check Laravel logs
tail -f storage/logs/laravel.log

// Check gift settings
$settings = GiftSettingsModel::first();
var_dump($settings->toArray());

// Check wallet balance
$walletService = app(\App\Services\WalletService::class);
$balance = $walletService->calculateBalance($user);
echo "Balance: K$balance";
```

### Announcement Not Created
```php
// Check if announcement exists
$announcement = \App\Infrastructure\Persistence\Eloquent\Announcement\AnnouncementModel::where('user_id', $recipientId)
    ->where('title', 'like', '%Gift%')
    ->latest()
    ->first();

if ($announcement) {
    echo "Announcement found: {$announcement->title}";
} else {
    echo "No announcement found";
}
```

## 📈 Analytics Queries

### Gift Statistics
```php
// Total gifts this month
$totalGifts = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitGiftModel::whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->count();

// Total amount gifted this month
$totalAmount = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitGiftModel::whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->sum('amount');

// Average gift amount
$avgAmount = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitGiftModel::avg('amount');

// Most popular tier
$tierStats = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitGiftModel::select('tier', \DB::raw('count(*) as count'))
    ->groupBy('tier')
    ->get();
```

## 🔐 Security Checks

### Verify Gift Eligibility
```php
// Check downline relationship
$policy = app(\App\Domain\StarterKit\Policies\GiftPolicy::class);
$isEligible = $policy->canReceiveGift($gifter, $recipient);

// Check if recipient already has kit
$hasKit = $recipient->starterKitPurchases()->exists();

// Check wallet balance
$walletService = app(\App\Services\WalletService::class);
$balance = $walletService->calculateBalance($gifter);
$hasSufficientBalance = $balance >= 500;
```

## 📱 Frontend Integration

### Check Gift Limits (JavaScript)
```javascript
const response = await axios.get('/mygrownet/gifts/limits');
const limits = response.data;

console.log('Remaining gifts:', limits.remaining_gifts);
console.log('Remaining amount:', limits.remaining_amount);
console.log('Current balance:', limits.current_wallet_balance);
```

### Gift Starter Kit (JavaScript)
```javascript
try {
    const response = await axios.post('/mygrownet/gifts/starter-kit', {
        recipient_id: 123,
        tier: 'basic'
    });
    
    console.log('Success:', response.data.message);
} catch (error) {
    console.error('Error:', error.response.data.message);
}
```

## 🎯 Key Metrics to Monitor

1. **Gift Conversion Rate**: Gifts → Active Members
2. **Average Gift Value**: Basic vs Premium ratio
3. **Top Gifters**: Who's building their team
4. **Gift Velocity**: Gifts per day/week/month
5. **Recipient Retention**: Do gifted members stay active?

## 📞 Support Contacts

- **Technical Issues**: Check Laravel logs
- **Business Logic**: Review `GiftService.php`
- **UI Issues**: Check browser console
- **Database Issues**: Review migrations and models

---

**Last Updated:** November 11, 2025  
**Version:** 1.0.0
