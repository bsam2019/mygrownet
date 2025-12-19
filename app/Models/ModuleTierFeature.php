<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleTierFeature extends Model
{
    protected $fillable = [
        'module_tier_id',
        'feature_key',
        'feature_name',
        'feature_type',
        'value_boolean',
        'value_limit',
        'value_text',
        'is_active',
    ];

    protected $casts = [
        'value_boolean' => 'boolean',
        'value_limit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(ModuleTier::class, 'module_tier_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getValue(): mixed
    {
        return match($this->feature_type) {
            'boolean' => $this->value_boolean,
            'limit' => $this->value_limit,
            'text' => $this->value_text,
            default => null,
        };
    }

    public function getDisplayValueAttribute(): string
    {
        return match($this->feature_type) {
            'boolean' => $this->value_boolean ? '✓' : '✗',
            'limit' => $this->value_limit === null ? 'Unlimited' : number_format($this->value_limit),
            'text' => $this->value_text ?? '-',
            default => '-',
        };
    }
}
