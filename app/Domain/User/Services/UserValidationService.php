<?php

namespace App\Domain\User\Services;

use App\Models\User;
use App\Domain\User\Exceptions\InvalidPhoneFormatException;
use App\Domain\User\ValueObjects\ValidationResult;

class UserValidationService
{
    /**
     * Validate user creation data
     */
    public function validateUserCreation(array $userData): ValidationResult
    {
        $errors = [];
        $validatedData = $userData;
        
        // Email validation
        if (empty($userData['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif ($this->emailExists($userData['email'])) {
            $errors[] = 'Email already exists';
        }
        
        // Phone validation and normalization
        if (!empty($userData['phone'])) {
            try {
                $normalizedPhone = $this->normalizePhone($userData['phone']);
                if ($this->phoneExists($normalizedPhone)) {
                    $errors[] = 'Phone number already exists';
                } else {
                    $validatedData['phone'] = $normalizedPhone;
                }
            } catch (InvalidPhoneFormatException $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        // Name validation
        if (empty($userData['name']) || strlen(trim($userData['name'])) < 2) {
            $errors[] = 'Name must be at least 2 characters long';
        } else {
            $validatedData['name'] = trim($userData['name']);
        }
        
        // Password validation
        if (empty($userData['password']) || strlen($userData['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        return new ValidationResult($errors, $validatedData);
    }
    
    /**
     * Normalize phone number to standard format
     */
    public function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^\d\+]/', '', $phone);
        
        // Handle different input formats
        if (preg_match('/^260\d{9}$/', $cleaned)) {
            return '+' . $cleaned;
        }
        
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return '+260' . substr($cleaned, 1);
        }
        
        if (preg_match('/^\+260\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        
        throw new InvalidPhoneFormatException("Invalid phone format: {$phone}. Expected format: +260XXXXXXXXX");
    }
    
    /**
     * Check if email already exists
     */
    private function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }
    
    /**
     * Check if phone number already exists
     */
    private function phoneExists(string $phone): bool
    {
        return User::where('phone', $phone)->exists();
    }
    
    /**
     * Validate user update data
     */
    public function validateUserUpdate(User $user, array $userData): ValidationResult
    {
        $errors = [];
        $validatedData = $userData;
        
        // Email validation (if being updated)
        if (isset($userData['email'])) {
            if (empty($userData['email'])) {
                $errors[] = 'Email cannot be empty';
            } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($userData['email'] !== $user->email && $this->emailExists($userData['email'])) {
                $errors[] = 'Email already exists';
            }
        }
        
        // Phone validation (if being updated)
        if (isset($userData['phone'])) {
            if (!empty($userData['phone'])) {
                try {
                    $normalizedPhone = $this->normalizePhone($userData['phone']);
                    if ($normalizedPhone !== $user->phone && $this->phoneExists($normalizedPhone)) {
                        $errors[] = 'Phone number already exists';
                    } else {
                        $validatedData['phone'] = $normalizedPhone;
                    }
                } catch (InvalidPhoneFormatException $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }
        
        // Name validation (if being updated)
        if (isset($userData['name'])) {
            if (empty($userData['name']) || strlen(trim($userData['name'])) < 2) {
                $errors[] = 'Name must be at least 2 characters long';
            } else {
                $validatedData['name'] = trim($userData['name']);
            }
        }
        
        return new ValidationResult($errors, $validatedData);
    }
    
    /**
     * Validate bulk user data for imports
     */
    public function validateBulkUsers(array $usersData): array
    {
        $results = [];
        $emails = [];
        $phones = [];
        
        foreach ($usersData as $index => $userData) {
            $errors = [];
            $validatedData = $userData;
            
            // Check for duplicate emails in batch
            if (!empty($userData['email'])) {
                if (in_array($userData['email'], $emails)) {
                    $errors[] = 'Duplicate email in batch';
                } else {
                    $emails[] = $userData['email'];
                    if ($this->emailExists($userData['email'])) {
                        $errors[] = 'Email already exists in database';
                    }
                }
            } else {
                $errors[] = 'Email is required';
            }
            
            // Check for duplicate phones in batch
            if (!empty($userData['phone'])) {
                try {
                    $normalizedPhone = $this->normalizePhone($userData['phone']);
                    if (in_array($normalizedPhone, $phones)) {
                        $errors[] = 'Duplicate phone in batch';
                    } else {
                        $phones[] = $normalizedPhone;
                        if ($this->phoneExists($normalizedPhone)) {
                            $errors[] = 'Phone already exists in database';
                        } else {
                            $validatedData['phone'] = $normalizedPhone;
                        }
                    }
                } catch (InvalidPhoneFormatException $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            $results[$index] = new ValidationResult($errors, $validatedData);
        }
        
        return $results;
    }
}