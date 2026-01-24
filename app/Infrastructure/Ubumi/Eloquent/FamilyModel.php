<?php

namespace App\Infrastructure\Ubumi\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Family Eloquent Model
 * 
 * Data layer representation of Family
 */
class FamilyModel extends Model
{
    protected $table = 'ubumi_families';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'admin_user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(PersonModel::class, 'family_id');
    }
}
