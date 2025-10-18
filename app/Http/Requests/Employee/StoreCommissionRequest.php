<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-commissions');
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'investment_id' => 'nullable|exists:investments,id',
            'user_id' => 'nullable|exists:users,id',
            'commission_type' => 'required|in:investment_facilitation,referral,performance_bonus,retention_bonus',
            'base_amount' => 'required|numeric|min:0|max:999999999.99',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'commission_amount' => 'required|numeric|min:0|max:999999999.99',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'investment_id.exists' => 'The selected investment does not exist.',
            'user_id.exists' => 'The selected user does not exist.',
            'commission_type.required' => 'Commission type is required.',
            'commission_type.in' => 'Invalid commission type selected.',
            'base_amount.required' => 'Base amount is required.',
            'base_amount.numeric' => 'Base amount must be a valid number.',
            'base_amount.min' => 'Base amount cannot be negative.',
            'base_amount.max' => 'Base amount is too large.',
            'commission_rate.required' => 'Commission rate is required.',
            'commission_rate.numeric' => 'Commission rate must be a valid number.',
            'commission_rate.min' => 'Commission rate cannot be negative.',
            'commission_rate.max' => 'Commission rate cannot exceed 100%.',
            'commission_amount.required' => 'Commission amount is required.',
            'commission_amount.numeric' => 'Commission amount must be a valid number.',
            'commission_amount.min' => 'Commission amount cannot be negative.',
            'commission_amount.max' => 'Commission amount is too large.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}