<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'type' => 'required|in:email,sms',
            'purpose' => 'required|string|max:50|in:withdrawal,sensitive_operation,login,tier_upgrade,profile_update'
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'OTP code is required',
            'token.size' => 'OTP code must be exactly 6 digits',
            'token.regex' => 'OTP code must contain only numbers',
            'type.required' => 'OTP type is required',
            'type.in' => 'OTP type must be either email or SMS',
            'purpose.required' => 'OTP purpose is required',
            'purpose.in' => 'Invalid OTP purpose specified'
        ];
    }

    protected function prepareForValidation(): void
    {
        // Remove any spaces or special characters from token
        if ($this->has('token')) {
            $this->merge(['token' => preg_replace('/[^0-9]/', '', $this->input('token'))]);
        }

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