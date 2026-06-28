<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizEmployeeInvitationModel extends Model
{
    protected $table = 'growbiz_employee_invitations';

    protected $fillable = [
        'employee_id',
        'manager_id',
        'email',
        'token',
        'code',
        'type',
        'status',
        'expires_at',
        'accepted_at',
        'accepted_by_user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(GrowBizEmployeeModel::class, 'employee_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function acceptedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'accepted_by_user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->where('expires_at', '>', now());
    }

    public function scopeByToken($query, string $token)
    {
        return $query->where('token', $token);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }
}
