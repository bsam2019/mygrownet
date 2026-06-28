<?php

namespace App\Infrastructure\Persistence\Eloquent\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MemberPaymentModel extends Model
{
    use HasFactory;

    protected $table = 'member_payments';

    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_reference',
        'phone_number',
        'account_name',
        'payment_type',
        'notes',
        'status',
        'admin_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
