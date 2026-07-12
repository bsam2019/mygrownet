<?php

namespace App\Listeners;

use App\Notifications\BackupSuccessful;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\Backup\Events\BackupWasSuccessful;

class SendBackupSuccessNotification
{
    public function handle(BackupWasSuccessful $event): void
    {
        Log::info('Backup success event received, sending notification to: ' . config('backup.notifications.mail.to'));
        
        Notification::route('mail', config('backup.notifications.mail.to'))
            ->notify(new BackupSuccessful($event));
            
        Log::info('Backup notification sent successfully');
    }
}
