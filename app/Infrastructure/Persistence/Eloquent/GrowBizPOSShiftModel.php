<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBizPOSShiftModel extends Model
{
    protected $table = 'growbiz_pos_shifts';

    protected $fillable = [
        'user_id',
        'employee_id',
        'shift_number',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_cash_sales',
        'total_mobile_sales',
        'total_card_sales',
        'transaction_count',
        'started_at',
        'ended_at',
        'opening_notes',
        'closing_notes',
        'status',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_mobile_sales' => 'decimal:2',
        'total_card_sales' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(GrowBizPOSSaleModel::class, 'shift_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(GrowBizEmployeeModel::class, 'employee_id');
    }
}
