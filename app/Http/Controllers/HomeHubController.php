<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Enums\AccountType;

class HomeHubController extends Controller
{
    /**
     * Display the Home Hub dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get available modules based on user's account types
        $availableModules = $user->getAllAvailableModules();
        
        // Get account types for display
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
            'availableModules' => $availableModules,
        ]);
    }
}
