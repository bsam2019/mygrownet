<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    protected $table = 'sa_cash_registers';

    protected $fillable = [
        'sa_company_id', 'register_date',
        'opening_balance', 'total_sales', 'total_expenses', 'total_banking',
        'expected_closing', 'actual_closing', 'variance',
        'status', 'opened_by', 'closed_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'register_date' => 'date',
            'opening_balance' => 'decimal:2',
            'total_sales' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'total_banking' => 'decimal:2',
            'expected_closing' => 'decimal:2',
            'actual_closing' => 'decimal:2',
            'variance' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'sa_cash_register_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'closed_by');
    }
}
