<?php

namespace App\Http\Middleware;

use App\Domain\Core\Models\Domain;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel;
use Illuminate\Support\Facades\Auth;
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

        // Check if this is a StockFlow company subdomain
        if ($request->attributes->has('stockflow_company_id')) {
            return 'stockflow';
        }

        // Resolve subdomain blade view from domains table
        $domain = Domain::where('domain', $host)
            ->where('is_active', true)
            ->first();
        if ($domain && $domain->application) {
            $view = $domain->application->slug;
            if (view()->exists($view)) {
                return $view;
            }
        }

        // Main domain path detection
        $path = $request->path();

        $pathMap = [
            'primeedge'                         => 'primeedge',
            'admin'                             => 'admin',
            'employee'                          => 'employee',
            'employees'                         => 'employee',
            'growbiz'                           => 'growbiz',
            'lifeplus'                          => 'lifephus',
            'growfinance'                       => 'growfinance',
            'growmarket'                        => 'marketplace',
        ];

        foreach ($pathMap as $prefix => $view) {
            if (str_starts_with($path, $prefix)) {
                return $view;
            }
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

        // Use the correct auth guard for StockFlow subdomains.
        // The default $request->user() uses the 'web' guard, but StockFlow
        // users authenticate via the 'stockflow' guard. Routes inside the
        // auth:stockflow middleware group switch the guard before Inertia
        // renders the response, but the dashboard (GET /) is outside that
        // group, so we must explicitly fall back to the stockflow guard.
        $user = $request->user();
        if (!$user && $request->attributes->has('stockflow_company_id')) {
            $user = Auth::guard('stockflow')->user();
        }

        // Prepare lightweight user payload with cached roles and permissions
        $authUser = null;
        if ($user) {
            // StockFlow users use SaUserModel which has no roles() relationship
            if ($user instanceof SaUserModel) {
                $authUser = $user->only(['id', 'name', 'email']);
            } else {
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
                        'application_roles' => $user->getAllApplicationRoles(),
                    ];
                });

                $authUser = array_merge(
                    $user->only(['id', 'name', 'email']),
                    $authData
                );
            }
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

        // Get stockflow company features for feature toggles
        $companyFeatures = null;
        $stockflowAccount = null;
        if ($request->session()->has('stockflow_company_id')) {
            try {
                $companyModel = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::find(
                    $request->session()->get('stockflow_company_id')
                );
                $settings = $companyModel?->settings;
                $companyFeatures = $settings['features_enabled'] ?? null;
                $stockflowAccount = $companyModel?->subdomain; // Get the subdomain slug (e.g., 'taradasi')
            } catch (\Exception $e) {
                $companyFeatures = null;
                $stockflowAccount = null;
            }
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
            'ziggy' => function () use ($request) {
                $ziggy = (new Ziggy)->toArray();
                return [
                    ...$ziggy,
                    'location' => $request->url(),
                ];
            },
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
            'companyFeatures' => $companyFeatures,
            'stockflowAccount' => $stockflowAccount, // Share subdomain slug for Ziggy routes
            'csrf_token' => csrf_token(),
            'supportStats' => $supportStats,
            'employee' => $employee,
        ];
    }
}
