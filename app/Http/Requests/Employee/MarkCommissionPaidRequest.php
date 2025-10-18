<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class MarkCommissionPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('mark-commissions-paid');
    }

    public function rules(): array
    {
        return [
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_date.required' => 'Payment date is required.',
            'payment_date.date' => 'Payment date must be a valid date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}