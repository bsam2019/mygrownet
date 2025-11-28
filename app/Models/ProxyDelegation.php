<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProxyDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegator_id',
        'delegate_id',
        'resolution_id',
        'is_general',
        'valid_from',
        'valid_until',
        'status',
        'instructions',
    ];

    protected $casts = [
        'is_general' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'delegator_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'delegate_id');
    }

    public function resolution(): BelongsTo
    {
        return $this->belongsTo(ShareholderResolution::class, 'resolution_id');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        $now = now();
        if ($now < $this->valid_from) {
            return false;
        }
        
        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }
        
        return true;
    }
}
