<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load('profile');

        return Inertia::render('Admin/Users/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'rank' => $user->rank,
                'balance' => $user->balance,
                'total_earnings' => $user->total_earnings,
                'profile' => [
                    'phone_number' => $user->profile?->phone_number,
                    'address' => $user->profile?->address,
                    'city' => $user->profile?->city,
                    'country' => $user->profile?->country,
                    'preferred_payment_method' => $user->profile?->preferred_payment_method,
                    'payment_details' => $user->profile?->payment_details,
                    'kyc_status' => $user->profile?->kyc_status
                ]
            ]
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'preferred_payment_method' => 'nullable|string|in:bank,mobile_money',
            'payment_details' => 'nullable|array'
        ]);

        $user = auth()->user();
        $user->update(['name' => $validated['name']]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            collect($validated)->except('name')->toArray()
        );

        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed'
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password'])
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    public function uploadKYC(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:id,passport,drivers_license',
            'document' => 'required|file|max:5120'
        ]);

        $path = $request->file('document')->store('kyc_documents', 'private');

        auth()->user()->profile()->update([
            'kyc_documents' => array_merge(
                (array) auth()->user()->profile->kyc_documents,
                [$validated['document_type'] => $path]
            ),
            'kyc_status' => 'pending'
        ]);

        return back()->with('success', 'Document uploaded successfully');
    }

}
