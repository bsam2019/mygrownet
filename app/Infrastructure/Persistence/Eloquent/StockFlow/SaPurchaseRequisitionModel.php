<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaPurchaseRequisitionModel extends Model
{
    protected $table = 'sa_purchase_requisitions';
    protected $fillable = ['sa_company_id', 'requisition_number', 'requested_by', 'approved_by', 'date_required', 'status', 'notes'];
    protected $casts = ['date_required' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(SaUserModel::class, 'requested_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(SaUserModel::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(SaRequisitionItemModel::class, 'sa_purchase_requisition_id'); }
}
