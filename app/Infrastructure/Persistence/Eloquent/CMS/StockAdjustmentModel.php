<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_stock_adjustments';

    protected $fillable = [
        'company_id',
        'adjustment_number',
        'adjustment_date',
        'adjustment_type',
        'reason',
        'created_by',
        'approved_by',
        'approved_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'approved_date' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItemModel::class, 'adjustment_id');
    }
}
