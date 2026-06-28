<?php

namespace App\Http\Middleware;

use App\Domain\LifePlus\Services\LifePlusAccessService;
use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to inject LifePlus access info into all LifePlus pages
 */
class InjectLifePlusAccess
{
    private const MODULE = 'lifeplus';

    public function __construct(
        private readonly LifePlusAccessService $accessService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user) {
            $accessInfo = $this->accessService->getAccessInfo($user);
            
            // Share access info with all Inertia pages
            Inertia::share('lifeplusAccess', $accessInfo);
            
            // Also store in request for controllers
            $request->attributes->set('lifeplusAccess', $accessInfo);
            
            // Share latest notifications for the header dropdown
            $notifications = $this->getLatestNotifications($user->id);
            Inertia::share('lifeplusNotifications', $notifications);
        }
        
        return $next($request);
    }

    /**
     * Get latest notifications for the dropdown
     */
    private function getLatestNotifications(int $userId): array
    {
        return NotificationModel::forUser($userId)
            ->where(function ($query) {
                $query->where('module', self::MODULE)
                      ->orWhere('module', 'core');
            })
            ->notArchived()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $this->mapCategoryToType($notification->category),
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->created_at->toISOString(),
                    'read' => $notification->read_at !== null,
                ];
            })
            ->toArray();
    }

    /**
     * Map notification category to frontend type
     */
    private function mapCategoryToType(?string $category): string
    {
        return match ($category) {
            'success', 'achievement', 'welcome' => 'success',
            'warning', 'alert' => 'warning',
            'reminder', 'task' => 'reminder',
            default => 'info',
        };
    }
}
