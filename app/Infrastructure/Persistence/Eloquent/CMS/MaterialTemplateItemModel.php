<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialTemplateItemModel extends Model
{
    protected $table = 'cms_material_template_items';

    protected $fillable = [
        'template_id',
        'material_id',
        'quantity_per_unit',
        'wastage_percentage',
        'notes',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:2',
        'wastage_percentage' => 'decimal:2',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MaterialTemplateModel::class, 'template_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function calculateQuantity(float $jobSize): float
    {
        $baseQuantity = $jobSize * $this->quantity_per_unit;
        $wastage = $baseQuantity * ($this->wastage_percentage / 100);
        return $baseQuantity + $wastage;
    }
}
