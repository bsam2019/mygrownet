<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class EngagementDeliverableModel extends Model
{
    protected $table = 'prime_edge_engagement_deliverables';

    protected $fillable = [
        'id',
        'engagement_id',
        'name',
        'description',
        'file_path',
        'status',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
