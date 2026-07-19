<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItemModel extends Model
{
    protected $table = 'cms_purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'material_id',
        'job_material_plan_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'received_quantity' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(MaterialPurchaseOrderModel::class, 'purchase_order_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function jobMaterialPlan(): BelongsTo
    {
        return $this->belongsTo(JobMaterialPlanModel::class, 'job_material_plan_id');
    }

    public function calculateTotalPrice(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
    }

    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    public function isPartiallyReceived(): bool
    {
        return $this->received_quantity > 0 && $this->received_quantity < $this->quantity;
    }

    public function getPendingQuantity(): float
    {
        return max(0, $this->quantity - $this->received_quantity);
    }
}
