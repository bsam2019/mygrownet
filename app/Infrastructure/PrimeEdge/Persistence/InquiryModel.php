<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class InquiryModel extends Model
{
    protected $table = 'prime_edge_inquiries';

    protected $fillable = [
        'id',
        'client_id',
        'service_description',
        'preferred_service_type',
        'status',
        'quoted_amount',
        'quoted_currency',
        'quote_notes',
        'notes',
        'quoted_at',
        'responded_at',
    ];

    protected $casts = [
        'quoted_amount' => 'float',
        'quoted_at' => 'datetime',
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
