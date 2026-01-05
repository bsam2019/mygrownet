<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiring';
    protected $description = 'Check for expiring subscriptions and send reminder notifications';

    public function handle()
    {
        $this->info('Checking for expiring subscriptions...');
        
        $now = Carbon::now();
        
        // Check for subscriptions expiring in 7 days
        $this->checkExpiringIn(7);
        
        // Check for subscriptions expiring in 3 days
        $this->checkExpiringIn(3);
        
        // Check for subscriptions expiring in 1 day
        $this->checkExpiringIn(1);
        
        // Check for expired subscriptions
        $this->checkExpired();
        
        $this->info('Subscription check complete!');
    }
    
    private function checkExpiringIn(int $days)
    {
        $targetDate = Carbon::now()->addDays($days)->startOfDay();
        $endDate = $targetDate->copy()->endOfDay();
        
        // Get users with active subscriptions expiring in X days
        $users = User::whereHas('subscription', function ($query) use ($targetDate, $endDate) {
            $query->where('status', 'active')
                ->whereBetween('end_date', [$targetDate, $endDate]);
        })->with('subscription')->get();
        
        foreach ($users as $user) {
            // Check if we already sent this reminder
            $alreadySent = $user->notifications()
                ->where('type', 'subscription.expiring_soon')
                ->where('created_at', '>=', Carbon::now()->subHours(12))
                ->where('data->days_remaining', $days)
                ->exists();
            
            if ($alreadySent) {
                continue;
            }
            
            try {
                app(SendNotificationUseCase::class)->execute(
                    userId: $user->id,
                    type: 'subscription.expiring_soon',
                    data: [
                        'title' => 'Subscription Expiring Soon',
                        'message' => "Your subscription expires in {$days} day" . ($days > 1 ? 's' : '') . ". Renew now to keep your benefits!",
                        'days_remaining' => $days,
                        'expires_at' => $user->subscription->end_date->format('M d, Y'),
                        'action_url' => route('mygrownet.membership.show'),
                        'action_text' => 'Renew Now'
                    ]
                );
                
                $this->info("Sent {$days}-day reminder to {$user->name}");
            } catch (\Exception $e) {
                Log::warning('Failed to send expiring subscription notification', [
                    'user_id' => $user->id,
                    'days' => $days,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function checkExpired()
    {
        // Get users with active subscriptions that have passed their end date
        $users = User::whereHas('subscription', function ($query) {
            $query->where('status', 'active')
                ->where('end_date', '<', Carbon::now());
        })->with('subscription')->get();
        
        foreach ($users as $user) {
            // Update subscription status
            $user->subscription->update(['status' => 'expired']);
            
            try {
                app(SendNotificationUseCase::class)->execute(
                    userId: $user->id,
                    type: 'subscription.expired',
                    data: [
                        'title' => 'Subscription Expired',
                        'message' => 'Your subscription has expired. Renew now to restore your access and benefits!',
                        'expired_at' => $user->subscription->end_date->format('M d, Y'),
                        'action_url' => route('mygrownet.membership.show'),
                        'action_text' => 'Renew Subscription'
                    ]
                );
                
                $this->info("Sent expiry notification to {$user->name}");
            } catch (\Exception $e) {
                Log::warning('Failed to send expired subscription notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
