<?php

namespace App\Http\Requests\QuickInvoice;

use Illuminate\Foundation\Http\FormRequest;

class UploadLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow guest access
    }

    public function rules(): array
    {
        return [
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'Please select a logo file',
            'logo.image' => 'The file must be an image',
            'logo.mimes' => 'Logo must be PNG, JPG, or SVG format',
            'logo.max' => 'Logo must be less than 2MB',
        ];
    }
}
