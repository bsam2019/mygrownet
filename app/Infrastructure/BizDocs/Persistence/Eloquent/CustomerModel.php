<?php

namespace App\Infrastructure\BizDocs\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerModel extends Model
{
    use SoftDeletes;

    protected $table = 'bizdocs_customers';

    protected $fillable = [
        'business_id',
        'name',
        'address',
        'phone',
        'email',
        'tpin',
        'notes',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessProfileModel::class, 'business_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentModel::class, 'customer_id');
    }
}
