<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Support\Facades\Log;

/**
 * Auto Login to CMS Middleware
 * 
 * Automatically creates/links CMS user for admins accessing CMS features
 * from the admin dashboard. Provides seamless access without separate login.
 */
class AutoLoginToCMS
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        Log::info("AutoLoginToCMS: Middleware called", [
            'user_id' => $user?->id,
            'has_user' => $user !== null,
            'is_admin' => $user ? ($user->hasRole('admin') || $user->hasRole('Administrator')) : false,
        ]);
        
        // Only process for authenticated users with admin role
        if ($user && ($user->hasRole('admin') || $user->hasRole('Administrator'))) {
            try {
                Log::info("AutoLoginToCMS: Processing admin user {$user->id}");
                
                // Get or create platform company
                $platformCompany = $this->getPlatformCompany();
                Log::info("AutoLoginToCMS: Platform company", ['id' => $platformCompany->id]);
                
                // Get or create CMS user for this admin
                $cmsUser = $this->ensureCmsUser($user, $platformCompany);
                Log::info("AutoLoginToCMS: CMS user", ['id' => $cmsUser->id]);
                
                // Refresh the user's cmsUser relationship
                $user->load('cmsUser');
                Log::info("AutoLoginToCMS: User relationship refreshed");
                
                // Set CMS session variables
                session([
                    'cms_user_id' => $cmsUser->id,
                    'cms_company_id' => $platformCompany->id,
                    'cms_auto_login' => true,
                ]);
                
                Log::info("Auto-login to CMS successful for user {$user->id}");
                
            } catch (\Exception $e) {
                Log::error("Auto-login to CMS failed for user {$user->id}: {$e->getMessage()}", [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Don't block the request, just log the error
                // User can still access admin features
            }
        } else {
            Log::info("AutoLoginToCMS: Skipping - not admin or not authenticated");
        }
        
        return $next($request);
    }

    /**
     * Get or create the MyGrowNet platform company in CMS
     */
    private function getPlatformCompany(): CompanyModel
    {
        return CompanyModel::firstOrCreate(
            ['name' => 'MyGrowNet Platform'],
            [
                'industry_type' => 'technology',
                'email' => 'finance@mygrownet.com',
                'phone' => '+260-XXX-XXXXXX',
                'address' => 'Lusaka',
                'city' => 'Lusaka',
                'country' => 'Zambia',
                'status' => 'active',
                'subscription_type' => 'partner',
            ]
        );
    }

    /**
     * Ensure CMS user exists for the admin
     */
    private function ensureCmsUser($user, CompanyModel $company): CmsUserModel
    {
        $cmsUser = CmsUserModel::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->first();

        if (!$cmsUser) {
            // Get or create owner role for this company
            $ownerRole = $this->ensureOwnerRole($company);
            
            $cmsUser = CmsUserModel::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'role_id' => $ownerRole->id,
                'status' => 'active',
                'employee_number' => 'ADMIN-' . $user->id,
            ]);
            
            Log::info("Created CMS user {$cmsUser->id} for admin {$user->id}");
        } elseif (!$cmsUser->isActive()) {
            // Reactivate if inactive
            $cmsUser->update(['status' => 'active']);
            Log::info("Reactivated CMS user {$cmsUser->id} for admin {$user->id}");
        }

        return $cmsUser;
    }
    
    /**
     * Ensure owner role exists for the company
     */
    private function ensureOwnerRole(CompanyModel $company)
    {
        return \App\Infrastructure\Persistence\Eloquent\CMS\RoleModel::firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Owner',
            ],
            [
                'description' => 'Full system access',
                'permissions' => ['*'], // All permissions
                'is_system_role' => true,
            ]
        );
    }
}
