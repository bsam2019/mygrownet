<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizBusinessProfileModel extends Model
{
    protected $table = 'growbiz_business_profiles';

    protected $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'industry',
        'description',
        'logo_path',
        'phone',
        'email',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_id',
        'registration_number',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'industry' => $this->industry,
            'description' => $this->description,
            'logo_path' => $this->logo_path,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'settings' => $this->settings,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
