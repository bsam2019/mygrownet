<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkChangeHistory extends Model
{
    protected $table = 'network_change_history';
    
    protected $fillable = [
        'user_id',
        'old_referrer_id',
        'new_referrer_id',
        'target_referrer_id',
        'performed_by',
        'is_spillover',
        'reason',
        'metadata',
    ];
    
    protected $casts = [
        'is_spillover' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function oldReferrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_referrer_id');
    }
    
    public function newReferrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_referrer_id');
    }
    
    public function targetReferrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_referrer_id');
    }
    
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
