<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusChilimbaContributionTypeModel extends Model
{
    protected $table = 'lifeplus_chilimba_contribution_types';

    protected $fillable = [
        'group_id',
        'name',
        'icon',
        'default_amount',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaGroupModel::class, 'group_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaSpecialContributionModel::class, 'type_id');
    }
}
