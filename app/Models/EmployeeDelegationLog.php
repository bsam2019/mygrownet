<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDelegationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegation_id',
        'employee_id',
        'permission_key',
        'action',
        'performed_by',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public const ACTION_GRANTED = 'granted';
    public const ACTION_REVOKED = 'revoked';
    public const ACTION_USED = 'used';
    public const ACTION_APPROVAL_REQUESTED = 'approval_requested';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_EXPIRED = 'expired';

    public function delegation(): BelongsTo
    {
        return $this->belongsTo(EmployeeDelegation::class, 'delegation_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
