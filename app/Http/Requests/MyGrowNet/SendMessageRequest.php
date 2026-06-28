<?php

namespace App\Http\Requests\MyGrowNet;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'recipient_id' => ['required', 'integer', 'exists:users,id', 'different:' . auth()->id()],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'parent_id' => ['nullable', 'integer', 'exists:messages,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_id.required' => 'Please select a recipient',
            'recipient_id.exists' => 'The selected recipient does not exist',
            'recipient_id.different' => 'You cannot send a message to yourself',
            'subject.required' => 'Please enter a subject',
            'subject.max' => 'Subject cannot exceed 255 characters',
            'body.required' => 'Please enter a message',
            'body.max' => 'Message cannot exceed 10,000 characters',
        ];
    }
}
