<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuttingListItemModel extends Model
{
    protected $table = 'cms_cutting_list_items';

    protected $fillable = [
        'cutting_list_id',
        'material_id',
        'item_code',
        'description',
        'required_length',
        'quantity',
        'total_length',
        'stock_length',
        'pieces_per_stock',
        'waste_per_stock',
        'sort_order',
        'cut',
        'cut_at',
        'cut_by',
        'notes',
    ];

    protected $casts = [
        'required_length' => 'decimal:2',
        'total_length' => 'decimal:2',
        'waste_per_stock' => 'decimal:2',
        'cut' => 'boolean',
        'cut_at' => 'datetime',
    ];

    public function cuttingList(): BelongsTo
    {
        return $this->belongsTo(CuttingListModel::class, 'cutting_list_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function cutByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'cut_by');
    }
}
