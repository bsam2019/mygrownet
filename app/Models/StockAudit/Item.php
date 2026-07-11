<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $table = 'sa_items';

    protected $fillable = [
        'sa_company_id', 'sa_department_id', 'sa_bin_id',
        'name', 'sku', 'description', 'unit_price', 'unit',
        'system_quantity', 'reorder_level', 'category',
        'is_expirable', 'expiry_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'system_quantity' => 'decimal:2',
            'reorder_level' => 'decimal:2',
            'is_expirable' => 'boolean',
            'expiry_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'sa_department_id');
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'sa_bin_id');
    }
}
