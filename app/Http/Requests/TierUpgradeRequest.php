<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\InvestmentTier;

class TierUpgradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'target_tier_id' => [
                'required',
                'exists:investment_tiers,id',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $targetTier = InvestmentTier::find($value);
                    $currentTier = $user->getCurrentInvestmentTier();
                    
                    if ($targetTier && $currentTier && $targetTier->minimum_investment <= $currentTier->minimum_investment) {
                        $fail('You can only upgrade to a higher tier.');
                    }
                },
            ],
            'additional_amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->target_tier_id) {
                        $user = auth()->user();
                        $targetTier = InvestmentTier::find($this->target_tier_id);
                        
                        if ($targetTier) {
                            $totalAfterUpgrade = $user->total_investment_amount + $value;
                            if ($totalAfterUpgrade < $targetTier->minimum_investment) {
                                $requiredAmount = $targetTier->minimum_investment - $user->total_investment_amount;
                                $fail("You need to invest at least K{$requiredAmount} to upgrade to {$targetTier->name} tier.");
                            }
                        }
                    }
                },
            ],
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'payment_method' => 'required|string|in:bank_transfer,mobile_money,cash',
        ];
    }

    public function messages(): array
    {
        return [
            'target_tier_id.required' => 'Please select a target tier for upgrade.',
            'target_tier_id.exists' => 'The selected tier is invalid.',
            'additional_amount.required' => 'Additional investment amount is required.',
            'additional_amount.numeric' => 'Additional amount must be a valid number.',
            'additional_amount.min' => 'Additional amount must be at least K1.',
            'payment_proof.required' => 'Payment proof is required for tier upgrade.',
            'payment_proof.image' => 'Payment proof must be an image.',
            'payment_proof.max' => 'Payment proof must not exceed 2MB.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
        ];
    }

    public function attributes(): array
    {
        return [
            'target_tier_id' => 'target tier',
            'additional_amount' => 'additional investment amount',
            'payment_proof' => 'payment proof',
            'payment_method' => 'payment method',
        ];
    }
}