<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalCount extends Model
{
    protected $table = 'sa_physical_counts';

    protected $fillable = [
        'sa_company_id', 'title', 'count_date', 'status',
        'counted_by', 'verified_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'count_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'counted_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CountItem::class, 'sa_physical_count_id');
    }
}
