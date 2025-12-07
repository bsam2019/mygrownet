<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostTeamMemberModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_team_members';

    protected $fillable = [
        'business_id',
        'user_id',
        'name',
        'email',
        'role',
        'permissions',
        'location_id',
        'status',
        'invited_at',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(BizBoostLocationModel::class, 'location_id');
    }
}
