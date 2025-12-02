<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_tier' => ['required', 'string'],
            'additional_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_tier.required' => 'Please select a new tier.',
            'additional_amount.required' => 'Additional payment amount is required.',
            'additional_amount.min' => 'Additional amount must be positive.',
        ];
    }
}
