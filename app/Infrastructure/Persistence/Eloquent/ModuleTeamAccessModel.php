<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleTeamAccessModel extends Model
{
    protected $table = 'module_team_access';
    
    protected $fillable = [
        'subscription_id',
        'user_id',
        'role',
        'permissions',
        'invited_at',
        'accepted_at',
    ];
    
    protected $casts = [
        'permissions' => 'array',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];
    
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ModuleSubscriptionModel::class, 'subscription_id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function hasAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
    
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }
    
    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }
}
