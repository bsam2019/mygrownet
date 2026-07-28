<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceIntercompanyTransactionModel extends Model
{
    protected $table = 'growfinance_intercompany_transactions';

    protected $fillable = [
        'from_org_id',
        'to_org_id',
        'transaction_type',
        'reference',
        'description',
        'amount',
        'currency',
        'exchange_rate',
        'mapping',
        'status',
        'matched_transaction_id',
        'transaction_date',
    ];

    protected $casts = [
        'mapping' => 'array',
        'exchange_rate' => 'float',
        'transaction_date' => 'date',
    ];

    public function scopeFromOrg($query, int $orgId)
    {
        return $query->where('from_org_id', $orgId);
    }

    public function scopeToOrg($query, int $orgId)
    {
        return $query->where('to_org_id', $orgId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeMatched($query)
    {
        return $query->where('status', 'matched');
    }
}
