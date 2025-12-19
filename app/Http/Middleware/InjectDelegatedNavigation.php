<?php

namespace App\Http\Middleware;

use App\Domain\Employee\Constants\DelegatedPermissions;
use App\Domain\Employee\Services\DelegationService;
use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class InjectDelegatedNavigation
{
    public function __construct(
        protected DelegationService $delegationService
    ) {}

    /**
     * Handle an incoming request.
     * Injects delegated navigation items into Inertia shared data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Get employee record
        $employee = Employee::where('user_id', $user->id)
            ->where('employment_status', 'active')
            ->first();

        if (!$employee) {
            Inertia::share('delegatedNavigation', []);
            return $next($request);
        }

        // Get delegated permissions and build navigation
        $delegations = $this->delegationService->getEmployeeDelegations($employee);
        $navItems = $this->buildNavigationItems($delegations);

        Inertia::share('delegatedNavigation', $navItems);
        Inertia::share('hasDelegatedFunctions', count($navItems) > 0);

        return $next($request);
    }

    /**
     * Build navigation items from delegations
     */
    protected function buildNavigationItems($delegations): array
    {
        $permissionToNav = [
            // Support & Communication
            DelegatedPermissions::HANDLE_SUPPORT_TICKETS => [
                'name' => 'Support Center',
                'href' => 'employee.portal.delegated.support.index',
                'icon' => 'ChatBubbleLeftRightIcon',
                'category' => 'Support',
            ],
            
            // Finance
            DelegatedPermissions::VIEW_RECEIPTS => [
                'name' => 'Receipts',
                'href' => 'employee.portal.delegated.receipts.index',
                'icon' => 'DocumentTextIcon',
                'category' => 'Finance',
            ],
            DelegatedPermissions::VIEW_PAYMENTS => [
                'name' => 'Payments',
                'href' => 'employee.portal.delegated.payments.index',
                'icon' => 'CreditCardIcon',
                'category' => 'Finance',
            ],
            DelegatedPermissions::VIEW_WITHDRAWALS => [
                'name' => 'Withdrawals',
                'href' => 'employee.portal.delegated.withdrawals.index',
                'icon' => 'BanknotesIcon',
                'category' => 'Finance',
            ],
            
            // User Management
            DelegatedPermissions::VIEW_USERS => [
                'name' => 'Users',
                'href' => 'employee.portal.delegated.users.index',
                'icon' => 'UsersIcon',
                'category' => 'Users',
            ],
            
            // BGF
            DelegatedPermissions::VIEW_BGF_APPLICATIONS => [
                'name' => 'BGF Applications',
                'href' => 'employee.portal.delegated.bgf.index',
                'icon' => 'FolderIcon',
                'category' => 'BGF',
            ],
            
            // Investor Relations
            DelegatedPermissions::VIEW_INVESTOR_MESSAGES => [
                'name' => 'Investor Messages',
                'href' => 'employee.portal.delegated.investors.messages',
                'icon' => 'EnvelopeIcon',
                'category' => 'Investors',
            ],
            DelegatedPermissions::VIEW_INVESTOR_DOCUMENTS => [
                'name' => 'Investor Documents',
                'href' => 'employee.portal.delegated.investors.documents',
                'icon' => 'DocumentChartBarIcon',
                'category' => 'Investors',
            ],
            
            // Analytics
            DelegatedPermissions::VIEW_MEMBER_ANALYTICS => [
                'name' => 'Member Analytics',
                'href' => 'employee.portal.delegated.analytics.members',
                'icon' => 'ChartBarIcon',
                'category' => 'Analytics',
            ],
            DelegatedPermissions::VIEW_FINANCIAL_REPORTS => [
                'name' => 'Financial Reports',
                'href' => 'employee.portal.delegated.analytics.financial',
                'icon' => 'DocumentChartBarIcon',
                'category' => 'Analytics',
            ],
        ];

        $navItems = [];

        foreach ($delegations as $delegation) {
            if (isset($permissionToNav[$delegation->permission_key])) {
                $navItem = $permissionToNav[$delegation->permission_key];
                $navItem['requires_approval'] = $delegation->requires_approval;
                $navItem['permission_key'] = $delegation->permission_key;
                $navItems[] = $navItem;
            }
        }

        // Group by category
        $grouped = [];
        foreach ($navItems as $item) {
            $category = $item['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'name' => $category,
                    'items' => [],
                ];
            }
            unset($item['category']);
            $grouped[$category]['items'][] = $item;
        }

        return array_values($grouped);
    }
}
