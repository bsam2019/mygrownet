<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Investment;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $investment = $this->route('investment');
        return auth()->check() && $investment && $investment->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'withdrawal_type' => [
                'required',
                'in:full,partial,emergency,profits_only',
                function ($attribute, $value, $fail) {
                    $investment = $this->route('investment');
                    if ($investment) {
                        $eligibility = $investment->isEligibleForWithdrawal($value);
                        if (!$eligibility['is_eligible']) {
                            $fail('Withdrawal not allowed: ' . implode(', ', $eligibility['reasons']));
                        }
                    }
                },
            ],
            'amount' => [
                'required_if:withdrawal_type,partial',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->withdrawal_type === 'partial' && $value) {
                        $investment = $this->route('investment');
                        if ($investment) {
                            $maxWithdrawable = $investment->getWithdrawableAmount('partial');
                            if ($value > $maxWithdrawable) {
                                $fail("Maximum withdrawable amount is K{$maxWithdrawable}");
                            }
                        }
                    }
                },
            ],
            'reason' => 'required_if:withdrawal_type,emergency|string|max:500',
            'otp_code' => 'required|string|size:6',
            'bank_account' => 'required_unless:withdrawal_type,emergency|string|max:255',
            'bank_name' => 'required_unless:withdrawal_type,emergency|string|max:100',
            'account_holder_name' => 'required_unless:withdrawal_type,emergency|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'withdrawal_type.required' => 'Please select a withdrawal type.',
            'withdrawal_type.in' => 'Invalid withdrawal type selected.',
            'amount.required_if' => 'Amount is required for partial withdrawals.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Minimum withdrawal amount is K1.',
            'reason.required_if' => 'Reason is required for emergency withdrawals.',
            'reason.max' => 'Reason must not exceed 500 characters.',
            'otp_code.required' => 'OTP verification code is required.',
            'otp_code.size' => 'OTP code must be exactly 6 digits.',
            'bank_account.required_unless' => 'Bank account number is required.',
            'bank_name.required_unless' => 'Bank name is required.',
            'account_holder_name.required_unless' => 'Account holder name is required.',
        ];
    }

    public function attributes(): array
    {
        return [
            'withdrawal_type' => 'withdrawal type',
            'otp_code' => 'OTP code',
            'bank_account' => 'bank account number',
            'bank_name' => 'bank name',
            'account_holder_name' => 'account holder name',
        ];
    }
}