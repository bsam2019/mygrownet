<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSSettingsModel extends Model
{
    protected $table = 'pos_settings';

    protected $fillable = [
        'user_id',
        'module_context',
        'receipt_header',
        'receipt_footer',
        'business_name',
        'business_address',
        'business_phone',
        'tax_id',
        'default_tax_rate',
        'enable_tax',
        'require_customer',
        'allow_credit_sales',
        'auto_print_receipt',
        'track_inventory',
        'currency',
        'currency_symbol',
        'payment_methods',
        'quick_amounts',
    ];

    protected $casts = [
        'default_tax_rate' => 'decimal:2',
        'enable_tax' => 'boolean',
        'require_customer' => 'boolean',
        'allow_credit_sales' => 'boolean',
        'auto_print_receipt' => 'boolean',
        'track_inventory' => 'boolean',
        'payment_methods' => 'array',
        'quick_amounts' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module_context', $module);
    }
}
