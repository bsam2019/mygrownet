<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowMartWarehouse extends Model
{
    use HasFactory;
    protected $table = 'growmart_warehouses';

    protected $fillable = [
        'name',
        'province',
        'city',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(GrowMartInventory::class, 'warehouse_id');
    }
}
