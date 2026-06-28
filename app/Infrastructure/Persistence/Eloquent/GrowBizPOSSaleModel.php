<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBizPOSSaleModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_pos_sales';

    protected $fillable = [
        'user_id',
        'shift_id',
        'customer_id',
        'sale_number',
        'customer_name',
        'customer_phone',
        'item_count',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'amount_paid',
        'change_given',
        'status',
        'notes',
        'currency',
        'served_by',
        'completed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(GrowBizPOSSaleItemModel::class, 'sale_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(GrowBizPOSShiftModel::class, 'shift_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(GrowBizBookingCustomerModel::class, 'customer_id');
    }

    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(GrowBizEmployeeModel::class, 'served_by');
    }
}
