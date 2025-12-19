<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusChilimbaAuditLogModel extends Model
{
    protected $table = 'lifeplus_chilimba_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaGroupModel::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
