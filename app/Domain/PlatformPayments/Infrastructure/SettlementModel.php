<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class SettlementModel extends Model
{
    protected $table = 'payment_settlements';

    protected $fillable = [
        'organization_id',
        'provider',
        'provider_settlement_id',
        'expected_amount',
        'actual_amount',
        'fee_amount',
        'currency',
        'status',
        'settlement_date',
        'reconciled_at',
        'discrepancy_notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'settlement_date' => 'datetime',
            'reconciled_at' => 'datetime',
        ];
    }
}
