<?php

namespace App\Extensions\Pharmacy\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaLotModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaControlledMedicineModel extends Model
{
    protected $table = 'sa_controlled_medicines';
    protected $fillable = ['sa_company_id', 'sa_item_id', 'sa_lot_id', 'transaction_type', 'quantity', 'balance_after', 'patient_name', 'patient_id_number', 'prescription_number', 'notes', 'staff_user_id'];
    protected $casts = ['quantity' => 'decimal:2', 'balance_after' => 'decimal:2'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function lot(): BelongsTo { return $this->belongsTo(SaLotModel::class, 'sa_lot_id'); }
    public function staff(): BelongsTo { return $this->belongsTo(SaUserModel::class, 'staff_user_id'); }
}
