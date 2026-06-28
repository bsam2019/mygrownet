<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class IncomeSourceModel extends Model
{
    protected $table = 'income_sources';

    protected $fillable = [
        'user_id',
        'source_type',
        'amount',
        'frequency',
        'next_expected_date',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_expected_date' => 'date',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
