<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusChilimbaLoanPaymentModel extends Model
{
    protected $table = 'lifeplus_chilimba_loan_payments';

    protected $fillable = [
        'loan_id',
        'recorded_by',
        'payment_date',
        'amount',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaLoanModel::class, 'loan_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
