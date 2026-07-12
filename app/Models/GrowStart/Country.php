<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $table = 'growstart_countries';

    protected $fillable = [
        'name',
        'code',
        'currency',
        'currency_symbol',
        'is_active',
        'pack_version',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function journeys(): HasMany
    {
        return $this->hasMany(UserJourney::class, 'country_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'country_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'country_id');
    }

    public function providers(): HasMany
    {
        return $this->hasMany(PartnerProvider::class, 'country_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
