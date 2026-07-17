<?php

namespace App\Extensions\Manufacturing\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaWorkOrderModel extends Model
{
    protected $table = 'sa_work_orders';
    protected $fillable = ['sa_company_id', 'sa_bom_id', 'sa_item_id', 'order_number', 'quantity', 'completed_quantity', 'scrapped_quantity', 'status', 'due_date', 'started_at', 'completed_at', 'notes'];
    protected $casts = ['quantity' => 'decimal:2', 'completed_quantity' => 'decimal:2', 'scrapped_quantity' => 'decimal:2', 'due_date' => 'date', 'started_at' => 'date', 'completed_at' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function bom(): BelongsTo { return $this->belongsTo(SaBillOfMaterialsModel::class, 'sa_bom_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function materialIssues(): HasMany { return $this->hasMany(SaWorkOrderMaterialIssueModel::class, 'sa_work_order_id'); }
}
