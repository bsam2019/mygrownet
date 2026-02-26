<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Module\GetUserModulesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GrowBiz\SetupController;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Domain\Wallet\Services\UnifiedWalletService;
use App\Enums\AccountType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeHubController extends Controller
{
    public function __construct(
        private GetUserModulesUseCase $getUserModulesUseCase,
        private UnifiedWalletService $walletService
    ) {}

    /**
     * Filter modules based on enabled configuration
     */
    private function filterEnabledModules(array $modules): array
    {
        $enabledModules = \App\Services\ModuleService::getEnabledModules();
        $enabledSlugs = array_keys($enabledModules);
        
        // Map module slugs to config keys
        $slugToConfigKey = [
            'grownet' => 'grownet',
            'mygrownet-core' => 'grownet',
            'mlm-dashboard' => 'grownet',
            'growbuilder' => 'growbuilder',
            'bizboost' => 'bizboost',
            'growfinance' => 'growfinance',
            'growbiz' => 'growbiz',
            'cms' => 'cms',
            'marketplace' => 'growmarket',
            'shop' => 'growmarket',
            'learning' => 'library',
            'education' => 'library',
            'lifeplus' => 'lifeplus',
            'health' => 'lifeplus',
            'wellness' => 'lifeplus',
            'ubumi' => 'ubumi',
            'mygrow-save' => 'wallet',
            'wallet' => 'wallet',
            'messaging' => 'messaging',
            'announcements' => 'announcements',
            'community' => 'community',
            'support' => 'support',
            'workshops' => 'workshops',
            'profit-sharing' => 'profit_sharing',
            'inventory' => 'inventory',
            'pos' => 'pos',
            'bgf' => 'bgf',
            'business-growth-fund' => 'bgf',
            'growbackup' => 'growbackup',
        ];
        
        return array_filter($modules, function($module) use ($enabledSlugs, $slugToConfigKey) {
            $slug = $module['slug'] ?? '';
            $configKey = $slugToConfigKey[$slug] ?? $slug;
            
            // Always show dashboard
            if ($slug === 'dashboard' || $configKey === 'dashboard') {
                return true;
            }
            
            return in_array($configKey, $enabledSlugs);
        });
    }

    /**
     * Display the Home Hub (module marketplace)
     * Accessible to both authenticated and public users
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // If user is not authenticated, show public view with all modules
        if (!$user) {
            // Get all modules for public view (no access granted)
            $moduleDTOs = $this->getUserModulesUseCase->execute(null);
            
            $modules = array_map(function($dto) {
                return $dto->toArray();
            }, $moduleDTOs);
            
            // Filter by enabled modules
            $modules = $this->filterEnabledModules($modules);
            
            return Inertia::render('HomeHub/Index', [
                'modules' => array_values($modules), // Re-index array
                'isPublic' => true,
            ]);
        }
        
        // Authenticated user flow
        // Get all modules with access status using DDD use case
        $moduleDTOs = $this->getUserModulesUseCase->execute($user);
        
        // Check setup status for modules that require it
        $growbizNeedsSetup = SetupController::needsSetup($user->id);
        $bizboostNeedsSetup = $this->bizboostNeedsSetup($user->id);
        
        $modules = array_map(function($dto) use ($growbizNeedsSetup, $bizboostNeedsSetup) {
            $arr = $dto->toArray();
            // Add setup route for GrowBiz if needed
            if ($arr['slug'] === 'growbiz' && $growbizNeedsSetup) {
                $arr['primary_route'] = '/growbiz/setup';
                $arr['needs_setup'] = true;
            }
            // Add setup route for BizBoost if needed
            if ($arr['slug'] === 'bizboost' && $bizboostNeedsSetup) {
                $arr['primary_route'] = '/bizboost/setup';
                $arr['needs_setup'] = true;
            }
            return $arr;
        }, $moduleDTOs);
        
        // Filter by enabled modules
        $modules = $this->filterEnabledModules($modules);
        
        // Get account types for display (existing functionality)
        $accountTypes = array_map(function($typeValue) {
            try {
                $type = AccountType::from($typeValue);
                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'description' => $type->description(),
                    'color' => $type->color(),
                    'icon' => $type->icon(),
                ];
            } catch (\ValueError $e) {
                return null;
            }
        }, $user->account_types);
        
        // Filter out null values
        $accountTypes = array_filter($accountTypes);
        
        // Get primary account type
        $primaryAccountType = $user->getPrimaryAccountType();

        // Check user roles for special access
        $isAdmin = $user->hasRole('Administrator') || $user->hasRole('admin');
        $isManager = $user->rank === 'manager' || in_array($user->rank, ['manager', 'regional_manager']);
        $isEmployee = $user->hasRole('employee') && \App\Models\Employee::where('user_id', $user->id)
            ->where('employment_status', 'active')
            ->exists();

        // Get wallet balance for quick display
        $walletBalance = $this->walletService->calculateBalance($user);

        return Inertia::render('HomeHub/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_types' => array_values($accountTypes),
                'primary_account_type' => $primaryAccountType ? [
                    'value' => $primaryAccountType->value,
                    'label' => $primaryAccountType->label(),
                    'color' => $primaryAccountType->color(),
                ] : null,
            ],
            'modules' => array_values($modules), // Re-index array after filtering
            'availableModules' => $user->getAllAvailableModules(), // Legacy support
            'isPublic' => false,
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'isEmployee' => $isEmployee,
            'walletBalance' => $walletBalance,
        ]);
    }

    /**
     * Check if user needs to complete BizBoost setup
     */
    private function bizboostNeedsSetup(int $userId): bool
    {
        $business = BizBoostBusinessModel::where('user_id', $userId)->first();
        
        // Needs setup if no business exists or onboarding not completed
        return !$business || !$business->onboarding_completed;
    }
}
