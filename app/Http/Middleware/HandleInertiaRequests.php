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

    public function rootView(Request $request): string
    {
        $host = $request->getHost();
        $map = [
            'bizboost.mygrownet.com'    => 'bizboost',
            'bizdocs.mygrownet.com'     => 'bizdocs',
            'growbuilder.mygrownet.com' => 'growbuilder',
            'venture.mygrownet.com'     => 'venture',
            'grownet.mygrownet.com'     => 'grownet',
            'growstorage.mygrownet.com' => 'growstorage',
            'growmart.mygrownet.com'    => 'growmart',
            'zamstay.mygrownet.com'     => 'zamstay',
            'cms.mygrownet.com'         => 'cms',
            'primeedge.mygrownet.com'   => 'primeedge',
        ];

        // Check if this is a StockFlow company subdomain
        if (!isset($map[$host]) && preg_match('/^[a-z0-9-]+\.mygrownet\.com$/i', $host)) {
            $subdomain = strtolower(explode('.', $host)[0]);
            // If not a known subdomain, check if it's a StockFlow company
            $knownSubdomains = array_keys($map);
            if (!in_array($subdomain . '.mygrownet.com', $knownSubdomains) && $subdomain !== 'www') {
                return 'app';
            }
        }

        // Main domain path detection
        if (!isset($map[$host]) && str_starts_with($request->path(), 'primeedge')) {
            return 'primeedge';
        }

        return $map[$host] ?? $this->rootView;
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
                if (!class_exists(\App\Models\Employee\EmployeeSupportTicket::class)) {
                    return null;
                }
                return [
                    'open' => \App\Models\Employee\EmployeeSupportTicket::where('status', 'open')->count(),
                    'in_progress' => \App\Models\Employee\EmployeeSupportTicket::where('status', 'in_progress')->count(),
                    'urgent' => \App\Models\Employee\EmployeeSupportTicket::where('priority', 'urgent')
                        ->whereIn('status', ['open', 'in_progress'])->count(),
                ];
            });
        }

        // Get employee data if user has an employee record
        $employee = null;
        if ($user) {
            $employee = \App\Models\Employee\Employee::where('user_id', $user->id)
                ->where('employment_status', 'active')
                ->first();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'routeName' => $request->route()?->getName(),
            'auth' => [
                'user' => $authUser,
                'client' => function () use ($request) {
                    if ($request->path() === 'primeedge' || str_starts_with($request->path(), 'primeedge/')) {
                        $client = $request->user('primeedge');
                        if ($client) {
                            $client->load('clientProfile');
                            return $client?->only(['id', 'name', 'email', 'phone', 'company']);
                        }
                    }
                    return null;
                },
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
                'generatedContent' => fn () => $request->session()->get('generatedContent'),
                'businessPlan' => fn () => $request->session()->get('businessPlan'),
                'chatResponse' => fn () => $request->session()->get('chatResponse'),
                'generated_password' => fn () => $request->session()->get('generated_password'),
                'generated_email' => fn () => $request->session()->get('generated_email'),
            ],
            'impersonate_admin_id' => fn () => $request->session()->get('impersonate_admin_id'),
            'supportStats' => $supportStats,
            'employee' => $employee,
        ];
    }
}
