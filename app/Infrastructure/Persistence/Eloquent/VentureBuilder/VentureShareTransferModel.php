<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureShareTransferModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'venture_share_transfers';

    protected $fillable = [
        'venture_id',
        'from_user_id',
        'to_user_id',
        'shares',
        'price_per_share',
        'total_value',
        'status',
        'reason',
        'admin_notes',
        'approved_by',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'shares' => 'decimal:6',
        'price_per_share' => 'decimal:2',
        'total_value' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(VentureModel::class, 'venture_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
