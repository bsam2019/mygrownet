<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'address',
        'city',
        'country',
        'preferred_payment_method',
        'payment_details',
        'kyc_status',
        'kyc_documents',
        'current_investment_tier'
    ];

    protected $casts = [
        'payment_details' => 'json',
        'kyc_documents' => 'json'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}