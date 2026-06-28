<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleSubscriptionModel extends Model
{
    protected $table = 'module_subscriptions';
    
    protected $fillable = [
        'user_id',
        'module_id',
        'subscription_tier',
        'status',
        'started_at',
        'trial_ends_at',
        'expires_at',
        'cancelled_at',
        'auto_renew',
        'billing_cycle',
        'amount',
        'currency',
        'user_limit',
        'storage_limit_mb',
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'amount' => 'decimal:2',
        'user_limit' => 'integer',
        'storage_limit_mb' => 'integer',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleModel::class, 'module_id');
    }
    
    public function teamAccess(): HasMany
    {
        return $this->hasMany(ModuleTeamAccessModel::class, 'subscription_id');
    }
    
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
    
    public function isTrial(): bool
    {
        return $this->status === 'trial' 
            && $this->trial_ends_at !== null 
            && $this->trial_ends_at->isFuture();
    }
}
