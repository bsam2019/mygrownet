<?php

namespace App\Models\Investor;

use Illuminate\Database\Eloquent\Model;

class InvestorPaymentMethod extends Model
{
    protected $fillable = [
        'investor_account_id',
        'method_type',
        'bank_name',
        'account_number',
        'account_name',
        'branch_code',
        'mobile_provider',
        'mobile_number',
        'is_primary',
        'is_verified',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
    ];
}
