<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\InvestmentTier;

class InvestmentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:500', // VBIF minimum investment
                function ($attribute, $value, $fail) {
                    if ($this->tier_id) {
                        $tier = InvestmentTier::find($this->tier_id);
                        if ($tier && $value < $tier->minimum_investment) {
                            $fail("Minimum investment for {$tier->name} tier is K{$tier->minimum_investment}");
                        }
                    }
                },
            ],
            'tier_id' => 'required|exists:investment_tiers,id',
            'referrer_code' => 'nullable|exists:users,referral_code',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'payment_method' => 'required|string|in:bank_transfer,mobile_money,cash',
            'terms_accepted' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The minimum investment amount is K500.',
            'tier_id.required' => 'Please select an investment tier.',
            'tier_id.exists' => 'The selected investment tier is invalid.',
            'referrer_code.exists' => 'The referrer code is invalid.',
            'payment_proof.required' => 'Payment proof is required.',
            'payment_proof.image' => 'Payment proof must be an image.',
            'payment_proof.max' => 'Payment proof must not exceed 2MB.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tier_id' => 'investment tier',
            'referrer_code' => 'referrer code',
            'payment_proof' => 'payment proof',
            'payment_method' => 'payment method',
            'terms_accepted' => 'terms and conditions',
        ];
    }
}