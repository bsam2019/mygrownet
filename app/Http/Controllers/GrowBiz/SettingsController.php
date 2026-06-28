<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowBizBusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Show settings index page
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = $this->getUserRole($user);
        
        $businessProfile = null;
        if ($userRole !== 'employee') {
            $businessProfile = GrowBizBusinessProfileModel::where('user_id', $user->id)->first();
        }

        return Inertia::render('GrowBiz/Settings/Index', [
            'businessProfile' => $businessProfile?->toArray(),
            'userRole' => $userRole,
        ]);
    }

    /**
     * Show business profile settings
     */
    public function business()
    {
        $user = Auth::user();
        $userRole = $this->getUserRole($user);
        
        // Only owners can access business settings
        if ($userRole === 'employee') {
            return redirect()->route('growbiz.settings.index')
                ->with('error', 'You do not have permission to access business settings.');
        }

        $businessProfile = GrowBizBusinessProfileModel::firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => null,
                'business_type' => null,
                'industry' => null,
            ]
        );

        return Inertia::render('GrowBiz/Settings/Business', [
            'businessProfile' => $businessProfile->toArray(),
        ]);
    }

    /**
     * Update business profile
     */
    public function updateBusiness(Request $request)
    {
        $user = Auth::user();
        $userRole = $this->getUserRole($user);
        
        if ($userRole === 'employee') {
            return redirect()->route('growbiz.settings.index')
                ->with('error', 'You do not have permission to update business settings.');
        }

        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        GrowBizBusinessProfileModel::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return back()->with('success', 'Business profile updated successfully.');
    }

    /**
     * Show profile settings page
     */
    public function profile()
    {
        $user = Auth::user();
        $userRole = $this->getUserRole($user);

        return Inertia::render('GrowBiz/Settings/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
            ],
            'userRole' => $userRole,
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Show security settings page
     */
    public function security()
    {
        $user = Auth::user();
        $userRole = $this->getUserRole($user);

        return Inertia::render('GrowBiz/Settings/Security', [
            'userRole' => $userRole,
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => bcrypt($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Determine user's role in GrowBiz
     */
    private function getUserRole($user): string
    {
        // Check if user is an employee
        $employeeRecord = GrowBizEmployeeModel::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($employeeRecord) {
            return $employeeRecord->hasSupervisorRole() ? 'supervisor' : 'employee';
        }

        // Check if user is a business owner
        $hasEmployees = GrowBizEmployeeModel::where('manager_id', $user->id)->exists();
        
        return $hasEmployees ? 'owner' : 'owner'; // Default to owner for new users
    }
}
