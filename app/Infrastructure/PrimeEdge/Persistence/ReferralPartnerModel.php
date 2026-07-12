<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class ReferralPartnerModel extends Model
{
    protected $table = 'prime_edge_referral_partners';

    protected $fillable = [
        'id',
        'name',
        'contact_person',
        'email',
        'phone',
        'type',
        'specialization',
        'active',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
