<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusGigModel extends Model
{
    protected $table = 'lifeplus_gigs';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'payment_amount',
        'location',
        'latitude',
        'longitude',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function poster(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LifePlusGigApplicationModel::class, 'gig_id');
    }
}
