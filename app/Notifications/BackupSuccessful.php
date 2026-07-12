<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
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
        $disk = Storage::disk($this->event->diskName);
        $files = $disk->files('MyGrowNet');
        $latestFile = end($files);
        
        $newestBackupSize = $latestFile ? $disk->size($latestFile) : 0;
        $totalStorage = collect($files)->sum(fn($file) => $disk->size($file));
        
        return (new MailMessage)
            ->subject('✅ Backup Successful - MyGrowNet')
            ->markdown('emails.backup-success', [
                'applicationName' => $this->event->backupName,
                'diskName' => $this->event->diskName,
                'newestBackupSize' => $newestBackupSize,
                'numberOfBackups' => count($files),
                'totalStorageUsed' => $totalStorage,
                'newestBackupDate' => $latestFile ? now() : null,
                'oldestBackupDate' => !empty($files) ? now()->subDays(count($files)) : null,
            ]);
    }
}
