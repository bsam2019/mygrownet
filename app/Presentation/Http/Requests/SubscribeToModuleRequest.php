<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeToModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'module_id' => ['required', 'string', 'exists:modules,id'],
            'tier' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'in:ZMW,USD'],
            'billing_cycle' => ['sometimes', 'string', 'in:monthly,quarterly,annual'],
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'Please select a module.',
            'module_id.exists' => 'The selected module does not exist.',
            'tier.required' => 'Please select a subscription tier.',
            'amount.required' => 'Subscription amount is required.',
            'amount.min' => 'Subscription amount must be positive.',
        ];
    }
}
