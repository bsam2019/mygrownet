<?php

namespace App\Extensions\Manufacturing\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaWorkOrderMaterialIssueModel extends Model
{
    protected $table = 'sa_work_order_material_issues';
    protected $fillable = ['sa_work_order_id', 'sa_item_id', 'quantity', 'unit_cost', 'issue_type'];
    protected $casts = ['quantity' => 'decimal:2', 'unit_cost' => 'decimal:2'];

    public function workOrder(): BelongsTo { return $this->belongsTo(SaWorkOrderModel::class, 'sa_work_order_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
