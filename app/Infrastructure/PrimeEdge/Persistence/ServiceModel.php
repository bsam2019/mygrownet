<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class ServiceModel extends Model
{
    protected $table = 'prime_edge_services';

    protected $fillable = [
        'id',
        'name',
        'description',
        'category',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
