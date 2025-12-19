<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
// use App\Http\Controllers\Admin\RewardAnalyticsController; // DISABLED - causing memory exhaustion
// use Illuminate\Http\Request; // DISABLED - not needed without controller

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Points System Scheduled Tasks
use Illuminate\Support\Facades\Schedule;

Schedule::command('points:reset-monthly')
    ->monthlyOn(1, '00:00')
    ->description('Reset monthly activity points');

Schedule::command('points:check-qualification')
    ->dailyAt('09:00')
    ->description('Check monthly qualification status');

Schedule::command('points:check-advancements')
    ->dailyAt('10:00')
    ->description('Check for level advancements');

// Subscription Management
Schedule::command('subscriptions:check-expiring')
    ->dailyAt('08:00')
    ->description('Check for expiring subscriptions and send reminders');

// Loyalty Growth Reward (LGR) System
Schedule::command('lgr:process-daily-payouts')
    ->dailyAt('00:30')
    ->description('Process daily LGR payouts for active cycles');

Schedule::command('lgr:complete-cycles')
    ->dailyAt('01:00')
    ->description('Complete LGR cycles that have reached their end date');

Schedule::command('lgr:monitor-pool')
    ->dailyAt('06:00')
    ->description('Monitor LGR pool balance and send alerts if low');

// Account Integrity Monitoring
Schedule::command('accounts:monitor')
    ->dailyAt('07:00')
    ->description('Monitor account integrity and alert on issues');

// ========================================
// BizBoost Scheduled Tasks
// ========================================

// Process scheduled posts - runs every minute to publish posts when their scheduled time arrives
Schedule::command('bizboost:process-scheduled-posts')
    ->everyMinute()
    ->description('Publish BizBoost posts that are scheduled and ready');

// Process active campaigns - runs daily to schedule posts for each day of active campaigns
Schedule::command('bizboost:process-campaigns')
    ->dailyAt('06:00')
    ->description('Process active BizBoost campaigns and schedule daily posts');

// Auto-complete expired campaigns - runs daily to mark campaigns as completed
Schedule::command('bizboost:complete-expired-campaigns')
    ->dailyAt('00:30')
    ->description('Mark expired BizBoost campaigns as completed');

// Send follow-up reminders - runs every 15 minutes to send due reminder notifications
Schedule::command('bizboost:send-reminders')
    ->everyFifteenMinutes()
    ->description('Send due BizBoost follow-up reminder notifications');

// Refresh expiring social media tokens - runs daily to keep integrations active
Schedule::command('bizboost:refresh-tokens')
    ->dailyAt('03:00')
    ->description('Refresh expiring social media access tokens');

// ========================================
// Life+ Scheduled Tasks
// ========================================

// Send daily tip notification - runs every morning
Schedule::command('lifeplus:daily-tip')
    ->dailyAt('07:00')
    ->description('Send daily tip notification to Life+ users');

// Send task reminders - runs every hour
Schedule::command('lifeplus:task-reminders')
    ->hourly()
    ->description('Send task due reminders to Life+ users');

// Send habit reminders - runs every 5 minutes to catch reminder times
Schedule::command('lifeplus:habit-reminders')
    ->everyFiveMinutes()
    ->description('Send habit reminder notifications');

// ========================================
// Employee Delegation Scheduled Tasks
// ========================================

// Expire delegations that have passed their expiration date
Schedule::command('delegations:expire')
    ->dailyAt('00:15')
    ->description('Expire delegations that have passed their expiration date');

// DISABLED - RewardAnalyticsController causing circular dependency memory exhaustion
// Artisan::command('test:reward-analytics', function () {
//     $this->info('Testing RewardAnalyticsController...');
//     
//     try {
//         $controller = new RewardAnalyticsController();
//         $request = new Request();
//         
//         $response = $controller->index($request);
//         
//         $this->info('Response type: ' . get_class($response));
//         
//         if (method_exists($response, 'getData')) {
//             $data = $response->getData();
//             $this->info('Response data keys: ' . implode(', ', array_keys($data)));
//             
//             if (isset($data['analytics'])) {
//                 $this->info('Analytics data structure:');
//                 $this->line(json_encode(array_keys($data['analytics']), JSON_PRETTY_PRINT));
//             }
//             
//             if (isset($data['summary'])) {
//                 $this->info('Summary data structure:');
//                 $this->line(json_encode(array_keys($data['summary']), JSON_PRETTY_PRINT));
//             }
//         }
//         
//         $this->info('✅ Controller test successful');
//         
//     } catch (Exception $e) {
//         $this->error('❌ Error: ' . $e->getMessage());
//         $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
//     }
// })->purpose('Test the RewardAnalyticsController');
