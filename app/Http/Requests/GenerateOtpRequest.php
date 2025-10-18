<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:email,sms',
            'purpose' => 'required|string|max:50|in:withdrawal,sensitive_operation,login,tier_upgrade,profile_update',
            'identifier' => 'nullable|string|max:255',
            'metadata' => 'nullable|array'
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'OTP delivery type is required',
            'type.in' => 'OTP type must be either email or SMS',
            'purpose.required' => 'OTP purpose is required',
            'purpose.in' => 'Invalid OTP purpose specified',
            'identifier.string' => 'Identifier must be a valid string',
            'metadata.array' => 'Metadata must be a valid array'
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set default type to email if not provided
        if (!$this->has('type')) {
            $this->merge(['type' => 'email']);
        }

        // Set default purpose if not provided
        if (!$this->has('purpose')) {
            $this->merge(['purpose' => 'sensitive_operation']);
        }
    }
}