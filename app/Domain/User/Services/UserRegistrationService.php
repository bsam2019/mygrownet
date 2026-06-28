<?php

namespace App\Domain\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserRegistrationService
{
    public function __construct(
        private UserValidationService $validator
    ) {}

    /**
     * Register a new user with full validation and profile creation
     */
    public function registerUser(array $userData): User
    {
        // Validate all data before creation
        $validation = $this->validator->validateUserCreation($userData);
        
        if (!$validation->isValid()) {
            throw ValidationException::withMessages(
                array_combine(
                    array_map(fn($i) => "field_{$i}", range(0, count($validation->getErrors()) - 1)),
                    array_map(fn($e) => [$e], $validation->getErrors())
                )
            );
        }

        return DB::transaction(function () use ($validation, $userData) {
            $validatedData = $validation->getValidatedData();
            
            // Create user with validated data
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
                'password' => Hash::make($validatedData['password']),
                'referrer_id' => $userData['referrer_id'] ?? null,
            ]);
            
            // Always create profile immediately
            $this->createProfile($user, $userData);
            
            // Initialize points record
            $this->initializeUserPoints($user);
            
            // Log successful registration
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
            ]);
            
            return $user;
        });
    }

    /**
     * Create user profile
     */
    private function createProfile(User $user, array $userData): void
    {
        $user->profile()->create([
            'bio' => $userData['bio'] ?? null,
            'avatar' => $userData['avatar'] ?? null,
            'address' => $userData['address'] ?? null,
            'city' => $userData['city'] ?? null,
            'country' => $userData['country'] ?? 'Zambia',
        ]);
    }

    /**
     * Initialize user points record
     */
    private function initializeUserPoints(User $user): void
    {
        if (!$user->points()->exists()) {
            $user->points()->create([
                'lifetime_points' => 0,
                'monthly_activity_points' => 0,
                'last_activity_at' => now(),
            ]);
        }
    }

    /**
     * Update existing user with validation
     */
    public function updateUser(User $user, array $userData): User
    {
        $validation = $this->validator->validateUserUpdate($user, $userData);
        
        if (!$validation->isValid()) {
            throw ValidationException::withMessages(
                array_combine(
                    array_map(fn($i) => "field_{$i}", range(0, count($validation->getErrors()) - 1)),
                    array_map(fn($e) => [$e], $validation->getErrors())
                )
            );
        }

        $validatedData = $validation->getValidatedData();
        
        $user->update($validatedData);
        
        Log::info('User updated successfully', [
            'user_id' => $user->id,
            'updated_fields' => array_keys($validatedData),
        ]);
        
        return $user->fresh();
    }
}
