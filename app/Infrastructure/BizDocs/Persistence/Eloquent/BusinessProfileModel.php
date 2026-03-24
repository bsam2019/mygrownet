<?php

namespace App\Infrastructure\BizDocs\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfileModel extends Model
{
    protected $table = 'bizdocs_business_profiles';

    protected $fillable = [
        'user_id',
        'business_name',
        'logo',
        'address',
        'phone',
        'email',
        'tpin',
        'website',
        'bank_name',
        'bank_account',
        'bank_branch',
        'default_currency',
        'default_tax_rate',
        'default_terms',
        'default_notes',
        'default_payment_instructions',
        'signature_image',
        'stamp_image',
        'prepared_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(CustomerModel::class, 'business_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentModel::class, 'business_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(DocumentTemplateModel::class, 'owner_id');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(DocumentSequenceModel::class, 'business_id');
    }
}
