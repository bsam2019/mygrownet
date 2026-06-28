<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\AccountType;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     * Checks both account type and roles for module access.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Check access based on module
        $hasAccess = $this->checkModuleAccess($user, $module);
        
        if ($hasAccess) {
            return $next($request);
        }
        
        // User doesn't have access
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You do not have access to this module.',
                'module' => $module,
            ], 403);
        }
        
        // Redirect to home hub with error message
        return redirect()->route('home')->with('error', 'You do not have access to the ' . ucfirst($module) . ' module.');
    }
    
    /**
     * Check if user has access to a specific module
     * Based on account type and roles
     */
    private function checkModuleAccess($user, string $module): bool
    {
        $isAdmin = $user->hasRole('Administrator') || $user->hasRole('admin');
        
        // Admins have access to everything
        if ($isAdmin) {
            return true;
        }
        
        // Check if module is in user's available modules (based on account types)
        $availableModules = $user->getAllAvailableModules();
        if (in_array($module, $availableModules)) {
            return true;
        }
        
        // Legacy module checks for backward compatibility
        return match($module) {
            // MLM - only for MEMBER account type
            'mlm', 'mlm_dashboard' => $user->hasAccountType(AccountType::MEMBER),
            
            // Tasks - business accounts, employees, managers
            'tasks' => $user->hasAccountType(AccountType::BUSINESS) 
                || $user->hasAccountType(AccountType::EMPLOYEE)
                || $user->hasRole('employee') 
                || $user->hasRole('Manager'),
            
            // Accounting - business accounts, managers
            'accounting' => $user->hasAccountType(AccountType::BUSINESS) 
                || $user->hasRole('Manager'),
            
            // Training - members only (not clients)
            'training' => $user->hasAccountType(AccountType::MEMBER),
            
            // Marketplace - members and clients
            'marketplace' => $user->hasAccountType(AccountType::MEMBER) 
                || $user->hasAccountType(AccountType::CLIENT)
                || $user->hasAccountType(AccountType::BUSINESS),
            
            // Venture Builder - members, clients, investors
            'venture_builder' => $user->hasAccountType(AccountType::MEMBER) 
                || $user->hasAccountType(AccountType::CLIENT)
                || $user->hasAccountType(AccountType::INVESTOR),
            
            // Investor Portal - investors and members
            'investor_portal' => $user->hasAccountType(AccountType::INVESTOR)
                || $user->hasAccountType(AccountType::MEMBER),
            
            // Employee Portal - employees only
            'employee_portal' => $user->hasAccountType(AccountType::EMPLOYEE),
            
            // Live Chat - employees only
            'live_chat' => $user->hasAccountType(AccountType::EMPLOYEE),
            
            // Profile - everyone
            'profile', 'wallet' => true,
            
            // Unknown module - deny
            default => false,
        };
    }
    
    /**
     * Static helper to check if a user has access to a specific module
     * Can be used in controllers or views
     */
    public static function userHasAccess($user, string $module): bool
    {
        $instance = new self();
        return $instance->checkModuleAccess($user, $module);
    }
}
