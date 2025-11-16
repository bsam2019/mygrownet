<?php

namespace App\Http\Requests\MyGrowNet;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'in:technical,financial,account,general'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'subject' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Please select a category for your ticket',
            'subject.required' => 'Please provide a subject for your ticket',
            'subject.min' => 'Subject must be at least 5 characters',
            'description.required' => 'Please describe your issue',
            'description.min' => 'Description must be at least 10 characters',
        ];
    }
}
