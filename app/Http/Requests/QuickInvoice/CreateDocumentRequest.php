<?php

namespace App\Http\Requests\QuickInvoice;

use Illuminate\Foundation\Http\FormRequest;

class CreateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow guest access
    }

    public function rules(): array
    {
        return [
            'document_type' => 'required|in:invoice,delivery_note,quotation,receipt',
            'document_number' => 'nullable|string|max:50',
            'document_id' => 'nullable|string|uuid', // For editing existing documents
            
            // Business Info
            'business_name' => 'required|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:50',
            'business_email' => 'nullable|email|max:255',
            'business_logo' => 'nullable|string|max:500',
            'business_tax_number' => 'nullable|string|max:50',
            'business_website' => 'nullable|string|max:255',
            
            // Client Info
            'client_name' => 'required|string|max:255',
            'client_address' => 'nullable|string|max:500',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            
            // Dates
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            
            // Financial
            'currency' => 'nullable|in:ZMW,USD,EUR,GBP,ZAR',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            
            // Items
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            
            // Additional
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:1000',
            'save_document' => 'nullable|boolean',
            
            // Template & Styling
            'template' => 'nullable|in:classic,modern,minimal,professional,bold',
            'colors' => 'nullable|array',
            'colors.primary' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'colors.secondary' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'colors.accent' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'signature' => 'nullable|string|max:500',
            'prepared_by' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'business_name.required' => 'Please enter your business name',
            'client_name.required' => 'Please enter the client name',
            'items.required' => 'Please add at least one item',
            'items.min' => 'Please add at least one item',
            'items.*.description.required' => 'Item description is required',
            'items.*.quantity.required' => 'Item quantity is required',
            'items.*.unit_price.required' => 'Item price is required',
        ];
    }
}
