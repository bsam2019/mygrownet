<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\DB;

/**
 * Listener to set the module column on notifications after they are sent.
 * This handles GrowFinance and other module-specific notifications.
 */
class SetNotificationModule
{
    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        // Only process database notifications
        if ($event->channel !== 'database') {
            return;
        }

        $notificationClass = get_class($event->notification);
        $module = $this->getModuleFromClass($notificationClass);

        if ($module && $event->response) {
            // Update the module column for this notification
            DB::table('notifications')
                ->where('id', $event->response->id)
                ->update(['module' => $module]);
        }
    }

    /**
     * Determine the module based on the notification class namespace.
     */
    private function getModuleFromClass(string $class): ?string
    {
        if (str_contains($class, 'Notifications\\GrowFinance\\')) {
            return 'growfinance';
        }

        if (str_contains($class, 'Notifications\\GrowBiz\\')) {
            return 'growbiz';
        }

        if (str_contains($class, 'Notifications\\MyGrowNet\\')) {
            return 'mygrownet';
        }

        return null; // Will use default 'core'
    }
}
