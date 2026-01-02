<?php

namespace App\Http\Requests\QuickInvoice;

class SendEmailRequest extends CreateDocumentRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'client_email' => 'required|email|max:255',
            'custom_message' => 'nullable|string|max:1000',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'client_email.required' => 'Client email is required to send via email',
        ]);
    }
}
