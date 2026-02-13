<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorRatingModel extends Model
{
    protected $table = 'cms_vendor_ratings';

    protected $fillable = [
        'company_id', 'vendor_id', 'purchase_order_id',
        'quality_rating', 'delivery_rating', 'communication_rating', 'pricing_rating',
        'overall_rating', 'comments', 'created_by',
    ];

    protected $casts = [
        'overall_rating' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendor_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'purchase_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }
}
