<?php

namespace App\Extensions\Restaurant\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaRecipeModel extends Model
{
    protected $table = 'sa_recipes';
    protected $fillable = ['sa_company_id', 'sa_item_id', 'name', 'yield_quantity', 'yield_uom', 'difficulty', 'prep_time_minutes', 'cook_time_minutes', 'instructions', 'allergens', 'dietary_labels', 'status'];
    protected $casts = ['yield_quantity' => 'decimal:2', 'allergens' => 'array', 'dietary_labels' => 'array'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function ingredients(): HasMany { return $this->hasMany(SaRecipeIngredientModel::class, 'sa_recipe_id'); }
}
