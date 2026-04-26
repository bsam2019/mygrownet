<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuttingListModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_cutting_lists';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'list_number',
        'generated_date',
        'status',
        'total_length_required',
        'total_length_used',
        'waste_percentage',
        'optimized',
        'notes',
    ];

    protected $casts = [
        'generated_date' => 'date',
        'total_length_required' => 'decimal:2',
        'total_length_used' => 'decimal:2',
        'waste_percentage' => 'decimal:2',
        'optimized' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CuttingListItemModel::class, 'cutting_list_id');
    }
}
