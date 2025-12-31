<?php

namespace App\Models\GrowBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class SitePaymentConfig extends Model
{
    protected $table = 'growbuilder_site_payment_configs';

    protected $fillable = [
        'site_id',
        'gateway',
        'credentials',
        'is_active',
        'test_mode',
        'webhook_secret',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'test_mode' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the site that owns this payment config
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::class, 'site_id');
    }

    /**
     * Get transactions for this config
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(SitePaymentTransaction::class, 'payment_config_id');
    }

    /**
     * Get decrypted credentials
     */
    public function decryptedCredentials(): array
    {
        try {
            return json_decode(Crypt::decryptString($this->credentials), true);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set encrypted credentials
     */
    public function setCredentialsAttribute(array|string $value): void
    {
        if (is_array($value)) {
            $this->attributes['credentials'] = Crypt::encryptString(json_encode($value));
        } else {
            $this->attributes['credentials'] = $value;
        }
    }

    /**
     * Scope to get active configs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get production configs
     */
    public function scopeProduction($query)
    {
        return $query->where('test_mode', false);
    }
}
