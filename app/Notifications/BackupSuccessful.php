<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Events\BackupWasSuccessful as BackupWasSuccessfulEvent;

class BackupSuccessful extends Notification
{
    use Queueable;

    public function __construct(
        public BackupWasSuccessfulEvent $event
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $backup = $this->event->backupDestination;
        $newestBackup = $backup->newestBackup();
        $oldestBackup = $backup->oldestBackup();
        
        return (new MailMessage)
            ->subject('✅ Backup Successful - MyGrowNet')
            ->markdown('emails.backup-success', [
                'applicationName' => $backup->backupName(),
                'diskName' => $backup->diskName(),
                'newestBackupSize' => $newestBackup ? $newestBackup->sizeInBytes() : 0,
                'numberOfBackups' => $backup->backups()->count(),
                'totalStorageUsed' => $backup->usedStorage(),
                'newestBackupDate' => $newestBackup ? $newestBackup->date() : null,
                'oldestBackupDate' => $oldestBackup ? $oldestBackup->date() : null,
            ]);
    }
}
