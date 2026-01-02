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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
