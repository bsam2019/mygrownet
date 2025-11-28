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
