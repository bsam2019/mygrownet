<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class EngagementModel extends Model
{
    protected $table = 'prime_edge_engagements';

    protected $fillable = [
        'id',
        'client_id',
        'type',
        'description',
        'scope',
        'status',
        'agreed_fee_amount',
        'agreed_fee_currency',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'agreed_fee_amount' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
