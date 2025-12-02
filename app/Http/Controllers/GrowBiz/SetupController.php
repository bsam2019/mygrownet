<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizBusinessProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SetupController extends Controller
{
    /**
     * Check if user needs GrowBiz setup
     */
    public static function needsSetup(int $userId): bool
    {
        return Cache::remember("growbiz_needs_setup_{$userId}", 300, function () use ($userId) {
            // Check if user is a business owner (has employees OR has a business profile)
            $hasEmployees = GrowBizEmployeeModel::where('manager_id', $userId)->exists();
            $hasProfile = GrowBizBusinessProfileModel::where('user_id', $userId)->exists();
            
            // Check if user is an employee (linked via invitation)
            $isEmployee = GrowBizEmployeeModel::where('user_id', $userId)
                ->where('status', 'active')
                ->exists();
            
            // Needs setup if neither owner nor employee
            return !$hasEmployees && !$hasProfile && !$isEmployee;
        });
    }

    /**
     * Clear setup cache for user
     */
    public static function clearSetupCache(int $userId): void
    {
        Cache::forget("growbiz_needs_setup_{$userId}");
        Cache::forget("growbiz_context_{$userId}");
    }

    /**
     * Show setup wizard
     */
    public function index()
    {
        $user = Auth::user();

        // If already set up, redirect to dashboard
        if (!self::needsSetup($user->id)) {
            return redirect()->route('growbiz.dashboard');
        }

        return Inertia::render('GrowBiz/Setup/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Complete setup as business owner
     */
    public function setupAsBusiness(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'team_size' => 'nullable|string|max:20',
            'owner_title' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        try {
            // Create or update business profile
            GrowBizBusinessProfileModel::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $validated['business_name'],
                    'industry' => $validated['industry'] ?? null,
                    'team_size' => $validated['team_size'] ?? null,
                    'owner_title' => $validated['owner_title'] ?? null,
                    'setup_completed_at' => now(),
                ]
            );

            // Clear setup cache
            self::clearSetupCache($user->id);

            Log::info('GrowBiz business setup completed', [
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
            ]);

            return redirect()->route('growbiz.dashboard')
                ->with('success', 'Welcome to GrowBiz! Your workspace is ready.');
        } catch (\Exception $e) {
            Log::error('GrowBiz setup failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to complete setup. Please try again.');
        }
    }
}
