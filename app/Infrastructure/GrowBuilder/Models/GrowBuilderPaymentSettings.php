<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class GrowBuilderPaymentSettings extends Model
{
    protected $table = 'growbuilder_payment_settings';

    protected $fillable = [
        'site_id',
        'momo_enabled',
        'momo_phone',
        'momo_api_user',
        'momo_api_key',
        'momo_subscription_key',
        'momo_sandbox',
        'airtel_enabled',
        'airtel_phone',
        'airtel_client_id',
        'airtel_client_secret',
        'airtel_sandbox',
        'cod_enabled',
        'whatsapp_enabled',
        'whatsapp_number',
        'bank_enabled',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
    ];

    protected $casts = [
        'momo_enabled' => 'boolean',
        'momo_sandbox' => 'boolean',
        'airtel_enabled' => 'boolean',
        'airtel_sandbox' => 'boolean',
        'cod_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'bank_enabled' => 'boolean',
    ];

    protected $hidden = [
        'momo_api_key',
        'airtel_client_secret',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    // Encrypt sensitive data
    public function setMomoApiKeyAttribute($value): void
    {
        $this->attributes['momo_api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getMomoApiKeyAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setAirtelClientSecretAttribute($value): void
    {
        $this->attributes['airtel_client_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAirtelClientSecretAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function getEnabledMethods(): array
    {
        $methods = [];

        if ($this->momo_enabled) {
            $methods[] = ['id' => 'momo', 'name' => 'MTN MoMo', 'icon' => 'momo'];
        }

        if ($this->airtel_enabled) {
            $methods[] = ['id' => 'airtel', 'name' => 'Airtel Money', 'icon' => 'airtel'];
        }

        if ($this->whatsapp_enabled) {
            $methods[] = ['id' => 'whatsapp', 'name' => 'WhatsApp Order', 'icon' => 'whatsapp'];
        }

        if ($this->cod_enabled) {
            $methods[] = ['id' => 'cod', 'name' => 'Cash on Delivery', 'icon' => 'cash'];
        }

        if ($this->bank_enabled) {
            $methods[] = ['id' => 'bank', 'name' => 'Bank Transfer', 'icon' => 'bank'];
        }

        return $methods;
    }

    public function getMomoConfig(): array
    {
        return [
            'api_user' => $this->momo_api_user,
            'api_key' => $this->momo_api_key,
            'subscription_key' => $this->momo_subscription_key,
            'sandbox' => $this->momo_sandbox,
        ];
    }

    public function getAirtelConfig(): array
    {
        return [
            'client_id' => $this->airtel_client_id,
            'client_secret' => $this->airtel_client_secret,
            'sandbox' => $this->airtel_sandbox,
        ];
    }
}
