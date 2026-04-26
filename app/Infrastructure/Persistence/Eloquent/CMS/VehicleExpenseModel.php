<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleExpenseModel extends Model
{
    protected $table = 'cms_vehicle_expenses';

    protected $fillable = [
        'vehicle_id',
        'expense_date',
        'expense_type',
        'amount',
        'description',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id');
    }
}
