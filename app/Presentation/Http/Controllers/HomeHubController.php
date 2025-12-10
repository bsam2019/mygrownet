<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Module\GetUserModulesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GrowBiz\SetupController;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Enums\AccountType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeHubController extends Controller
{
    public function __construct(
        private GetUserModulesUseCase $getUserModulesUseCase
    ) {}

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
            
            return Inertia::render('HomeHub/Index', [
                'modules' => $modules,
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
            'modules' => $modules,
            'availableModules' => $user->getAllAvailableModules(), // Legacy support
            'isPublic' => false,
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'isEmployee' => $isEmployee,
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
