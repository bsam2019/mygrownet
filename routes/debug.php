<?php

// Temporary debug route - REMOVE IN PRODUCTION
Route::get('/debug-dashboard', function() {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'is_admin' => $user->is_admin ?? false,
        'has_role_admin' => $user->hasRole('Administrator') ?? false,
        'preferred_dashboard' => $user->preferred_dashboard ?? 'not set',
        'routes' => [
            'dashboard' => route('dashboard'),
            'admin.dashboard' => $user->is_admin ? route('admin.dashboard') : 'N/A',
        ],
    ]);
})->middleware('auth');
