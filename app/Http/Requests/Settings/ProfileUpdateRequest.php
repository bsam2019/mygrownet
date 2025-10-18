<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isAdmin = $this->user()?->hasRole('admin') ?? false;

        return [
            'name' => ['required', 'string', 'max:255'],
            // Allow the email field to be present for non-admins without blocking submission.
            // We'll ignore it in the controller. Admins still get full validation.
            'email' => $isAdmin ? [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ] : [
                'sometimes', // present or omitted; no validation to avoid blocking name-only changes
            ],
        ];
    }
}
