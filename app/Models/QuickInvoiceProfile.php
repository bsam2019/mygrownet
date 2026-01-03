<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickInvoiceProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'signature',
        'tax_number',
        'default_tax_rate',
        'default_discount_rate',
        'default_notes',
        'default_terms',
    ];

    protected $casts = [
        'default_tax_rate' => 'float',
        'default_discount_rate' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
