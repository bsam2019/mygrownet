<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VentureCategoryModel extends Model
{
    use HasFactory;
    protected $table = 'venture_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function ventures(): HasMany
    {
        return $this->hasMany(VentureModel::class, 'category_id');
    }
}
