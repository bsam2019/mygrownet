<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class CalculateCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('calculate-commissions');
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'investment_id' => 'required|exists:investments,id',
            'commission_type' => 'required|in:investment_facilitation,referral,performance_bonus,retention_bonus',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'investment_id.required' => 'Investment ID is required.',
            'investment_id.exists' => 'The selected investment does not exist.',
            'commission_type.required' => 'Commission type is required.',
            'commission_type.in' => 'Invalid commission type selected.',
        ];
    }
}