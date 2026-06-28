<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ApproveCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('approve-commissions');
    }

    public function rules(): array
    {
        return [
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}