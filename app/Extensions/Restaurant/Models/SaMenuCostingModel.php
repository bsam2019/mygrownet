<?php

namespace App\Extensions\Restaurant\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaMenuCostingModel extends Model
{
    protected $table = 'sa_menu_costings';
    protected $fillable = ['sa_company_id', 'sa_recipe_id', 'total_ingredient_cost', 'cost_per_portion', 'suggested_price', 'target_food_cost_pct', 'costed_at'];
    protected $casts = ['total_ingredient_cost' => 'decimal:2', 'cost_per_portion' => 'decimal:2', 'suggested_price' => 'decimal:2', 'target_food_cost_pct' => 'decimal:2', 'costed_at' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function recipe(): BelongsTo { return $this->belongsTo(SaRecipeModel::class, 'sa_recipe_id'); }
}
