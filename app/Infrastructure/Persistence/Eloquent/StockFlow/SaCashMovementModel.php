<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaCashMovementModel extends Model
{
    protected $table = 'sa_cash_movements';
    protected $fillable = [
        'sa_company_id', 'sa_cash_register_id', 'type', 'amount', 'direction',
        'description', 'reference_type', 'reference_id', 'created_by',
    ];
    protected $casts = ['amount' => 'decimal:2'];

    public function cashRegister(): BelongsTo { return $this->belongsTo(SaCashRegisterModel::class, 'sa_cash_register_id'); }
}
