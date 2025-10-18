<?php

namespace App\Http\Requests;

use App\Models\IdVerification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IdVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'document_type' => [
                'required',
                Rule::in([
                    IdVerification::DOCUMENT_NATIONAL_ID,
                    IdVerification::DOCUMENT_PASSPORT,
                    IdVerification::DOCUMENT_DRIVERS_LICENSE,
                ]),
            ],
            'document_number' => [
                'required',
                'string',
                'max:50',
                // Unique check will be handled in controller for better error handling
            ],
            'document_front' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120', // 5MB
            ],
            'document_back' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120', // 5MB
            ],
            'selfie' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120', // 5MB
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'document_type.required' => 'Please select a document type.',
            'document_type.in' => 'Invalid document type selected.',
            'document_number.required' => 'Document number is required.',
            'document_number.max' => 'Document number cannot exceed 50 characters.',
            'document_front.required' => 'Front image of the document is required.',
            'document_front.image' => 'Document front must be an image file.',
            'document_front.mimes' => 'Document front must be a JPEG, PNG, or JPG file.',
            'document_front.max' => 'Document front image cannot exceed 5MB.',
            'document_back.image' => 'Document back must be an image file.',
            'document_back.mimes' => 'Document back must be a JPEG, PNG, or JPG file.',
            'document_back.max' => 'Document back image cannot exceed 5MB.',
            'selfie.image' => 'Selfie must be an image file.',
            'selfie.mimes' => 'Selfie must be a JPEG, PNG, or JPG file.',
            'selfie.max' => 'Selfie image cannot exceed 5MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'document_type' => 'document type',
            'document_number' => 'document number',
            'document_front' => 'document front image',
            'document_back' => 'document back image',
            'selfie' => 'selfie with document',
        ];
    }
}