<?php

namespace App\Extensions\Restaurant\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaRecipeIngredientModel extends Model
{
    protected $table = 'sa_recipe_ingredients';
    protected $fillable = ['sa_recipe_id', 'sa_item_id', 'quantity', 'uom', 'waste_factor', 'is_substitutable', 'sort_order'];
    protected $casts = ['quantity' => 'decimal:2', 'waste_factor' => 'decimal:2', 'is_substitutable' => 'boolean'];

    public function recipe(): BelongsTo { return $this->belongsTo(SaRecipeModel::class, 'sa_recipe_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
