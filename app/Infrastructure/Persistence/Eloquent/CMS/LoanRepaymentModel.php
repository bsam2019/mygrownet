<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepaymentModel extends Model
{
    protected $table = 'cms_loan_repayments';

    protected $fillable = [
        'loan_id',
        'user_id',
        'payment_reference',
        'payment_amount',
        'principal_portion',
        'interest_portion',
        'penalty_portion',
        'payment_date',
        'payment_method',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'principal_portion' => 'decimal:2',
        'interest_portion' => 'decimal:2',
        'penalty_portion' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoanReceivableModel::class, 'loan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
