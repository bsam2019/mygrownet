<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MaterialModel extends Model
{
    protected $table = 'cms_materials';

    protected $fillable = [
        'company_id',
        'category_id',
        'code',
        'name',
        'description',
        'unit',
        'current_price',
        'minimum_stock',
        'reorder_level',
        'supplier',
        'supplier_code',
        'lead_time_days',
        'specifications',
        'is_active',
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'lead_time_days' => 'integer',
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategoryModel::class, 'category_id');
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(MaterialPriceHistoryModel::class, 'material_id');
    }

    public function jobPlans(): HasMany
    {
        return $this->hasMany(JobMaterialPlanModel::class, 'material_id');
    }

    public function updatePrice(float $newPrice, int $changedBy, ?string $reason = null): void
    {
        $oldPrice = $this->current_price;
        
        if ($oldPrice != $newPrice) {
            $changePercentage = $oldPrice > 0 
                ? (($newPrice - $oldPrice) / $oldPrice) * 100 
                : 0;

            $this->priceHistory()->create([
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'change_percentage' => $changePercentage,
                'reason' => $reason,
                'changed_by' => $changedBy,
                'effective_date' => now(),
            ]);

            $this->update(['current_price' => $newPrice]);
        }
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
