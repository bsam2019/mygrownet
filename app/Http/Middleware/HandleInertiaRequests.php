<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the root view based on the request.
     */
    public function rootView(Request $request): string
    {
        // Use GrowBiz PWA template for GrowBiz routes
        if ($request->is('growbiz*')) {
            return 'growbiz';
        }

        return $this->rootView;
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        // Use manifest file hash for asset versioning
        // This ensures proper cache busting when assets change
        if (file_exists(public_path('build/manifest.json'))) {
            return md5_file(public_path('build/manifest.json'));
        }
        
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        // Prepare lightweight user payload with cached roles and permissions
        $authUser = null;
        if ($user) {
            // Use cache to avoid loading roles/permissions on every request
            $cacheKey = "user_auth_data_{$user->id}";
            $authData = cache()->remember($cacheKey, 300, function () use ($user) { // 5 min cache
                $roles = $user->roles()->pluck('slug')->toArray();
                $permissions = $user->roles()
                    ->with('permissions:id,slug')
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->pluck('slug')
                    ->unique()
                    ->values()
                    ->toArray();

                return [
                    'roles' => $roles,
                    'permissions' => $permissions,
                ];
            });

            $authUser = array_merge(
                $user->only(['id', 'name', 'email']),
                $authData
            );
        }

        // Get support stats for admin users
        $supportStats = null;
        if ($authUser && in_array('admin', $authUser['roles'] ?? [])) {
            $supportStats = cache()->remember('admin_support_stats', 60, function () {
                if (!class_exists(\App\Models\EmployeeSupportTicket::class)) {
                    return null;
                }
                return [
                    'open' => \App\Models\EmployeeSupportTicket::where('status', 'open')->count(),
                    'in_progress' => \App\Models\EmployeeSupportTicket::where('status', 'in_progress')->count(),
                    'urgent' => \App\Models\EmployeeSupportTicket::where('priority', 'urgent')
                        ->whereIn('status', ['open', 'in_progress'])->count(),
                ];
            });
        }

        // Get employee data if user has an employee record - CACHED
        $employee = null;
        if ($user) {
            $employee = cache()->remember("employee_record_{$user->id}", 300, function () use ($user) {
                return \App\Models\Employee::where('user_id', $user->id)
                    ->where('employment_status', 'active')
                    ->first();
            });
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $authUser,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'errors' => function () use ($request) {
                return $request->session()->get('errors')
                    ? $request->session()->get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'downloadUrl' => fn () => $request->session()->get('downloadUrl'),
                'invitation_code' => fn () => $request->session()->get('invitation_code'),
            ],
            'impersonate_admin_id' => fn () => $request->session()->get('impersonate_admin_id'),
            'supportStats' => $supportStats,
            'employee' => $employee,
            // GrowBiz PWA data - LAZY LOADED only for GrowBiz routes
            'growbiz' => fn () => $request->is('growbiz*') ? $this->getGrowBizData($request, $user) : null,
        ];
    }

    /**
     * Get GrowBiz data - only called for GrowBiz routes (lazy loaded)
     */
    private function getGrowBizData(Request $request, $user): array
    {
        return [
            'isPwa' => $this->isPwaMode($request),
            'userRole' => $this->getGrowBizUserRole($user),
            'unreadNotificationCount' => $user ? $this->getGrowBizUnreadNotifications($user) : 0,
            'unreadMessageCount' => $user ? $this->getGrowBizUnreadMessages($user) : 0,
        ];
    }

    /**
     * Detect if running in PWA standalone mode.
     */
    private function isPwaMode(Request $request): bool
    {
        return $request->header('X-PWA-Mode') === 'standalone' 
            || $request->query('pwa') === '1';
    }

    /**
     * Get user's role in GrowBiz (owner, supervisor, or employee) - CACHED
     */
    private function getGrowBizUserRole($user): string
    {
        if (!$user) {
            return 'none';
        }

        return cache()->remember("growbiz_role_{$user->id}", 300, function () use ($user) {
            // Check if user is a business owner (has employees under them)
            $isOwner = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('manager_id', $user->id)->exists();
            if ($isOwner) {
                return 'owner';
            }

            // Check if user is an employee
            $employeeRecord = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($employeeRecord) {
                // Check if they have direct reports (supervisor)
                if ($employeeRecord->hasSupervisorRole()) {
                    return 'supervisor';
                }
                return 'employee';
            }

            return 'none';
        });
    }

    /**
     * Get unread GrowBiz notifications count - CACHED for 60 seconds
     */
    private function getGrowBizUnreadNotifications($user): int
    {
        return cache()->remember("growbiz_notifications_{$user->id}", 60, function () use ($user) {
            return $user->unreadNotifications()
                ->where(function ($query) {
                    $query->where('type', 'like', '%GrowBiz%')
                        ->orWhere('data->type', 'like', 'growbiz_%');
                })
                ->count();
        });
    }

    /**
     * Get unread GrowBiz messages count - CACHED for 60 seconds
     */
    private function getGrowBizUnreadMessages($user): int
    {
        return cache()->remember("growbiz_messages_{$user->id}", 60, function () use ($user) {
            // Check if messaging table exists
            if (!\Schema::hasTable('messages')) {
                return 0;
            }

            // Get team member IDs for this user
            $teamMemberIds = $this->getGrowBizTeamMemberIds($user);
            
            if (empty($teamMemberIds)) {
                // No team members, just count [GrowBiz] prefixed messages
                return \DB::table('messages')
                    ->where('recipient_id', $user->id)
                    ->where('is_read', false)
                    ->where('subject', 'like', '[GrowBiz]%')
                    ->count();
            }

            return \DB::table('messages')
                ->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->where(function ($query) use ($teamMemberIds) {
                    // Messages from team members OR with [GrowBiz] prefix
                    $query->whereIn('sender_id', $teamMemberIds)
                        ->orWhere('subject', 'like', '[GrowBiz]%');
                })
                ->count();
        });
    }

    /**
     * Get team member IDs for GrowBiz messaging - CACHED
     */
    private function getGrowBizTeamMemberIds($user): array
    {
        return cache()->remember("growbiz_team_{$user->id}", 300, function () use ($user) {
            $ids = [];
            
            // Get employees managed by this user
            $employeeIds = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('manager_id', $user->id)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();
            $ids = array_merge($ids, $employeeIds);
            
            // Get manager if user is an employee
            $employeeRecord = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('user_id', $user->id)->first();
            if ($employeeRecord && $employeeRecord->manager_id) {
                $ids[] = $employeeRecord->manager_id;
                
                // Get colleagues (other employees under same manager)
                $colleagueIds = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('manager_id', $employeeRecord->manager_id)
                    ->whereNotNull('user_id')
                    ->where('user_id', '!=', $user->id)
                    ->pluck('user_id')
                    ->toArray();
                $ids = array_merge($ids, $colleagueIds);
            }
            
            return array_unique($ids);
        });
    }
}
