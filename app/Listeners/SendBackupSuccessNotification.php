<?php

namespace App\Listeners;

use App\Notifications\BackupSuccessful;
use Illuminate\Support\Facades\Notification;
use Spatie\Backup\Events\BackupWasSuccessful;

class SendBackupSuccessNotification
{
    public function handle(BackupWasSuccessful $event): void
    {
        Notification::route('mail', config('backup.notifications.mail.to'))
            ->notify(new BackupSuccessful($event));
    }
}
