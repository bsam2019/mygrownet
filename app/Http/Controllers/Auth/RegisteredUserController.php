<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StarterKitService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, StarterKitService $starterKitService): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:20|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
        ]);

        // Ensure at least one identifier (email or phone) is provided
        if (empty($request->email) && empty($request->phone)) {
            throw ValidationException::withMessages([
                'email' => 'Please provide either an email address or phone number.',
                'phone' => 'Please provide either an email address or phone number.',
            ]);
        }

        // Normalize phone number if provided
        $normalizedPhone = $request->phone ? User::normalizePhone($request->phone) : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $normalizedPhone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Process starter kit for new member
        $starterKitService->processStarterKit($user);

        Auth::login($user);

        return to_route('dashboard');
    }
}
