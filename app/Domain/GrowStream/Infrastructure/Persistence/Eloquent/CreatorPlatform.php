<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorPlatform extends Model
{
    use HasFactory;

    protected $table = 'growstream_creator_platforms';

    protected $fillable = [
        'organization_id',
        'subdomain',
        'custom_domain',
        'brand_name',
        'category',
        'brand_color',
        'logo_url',
        'banner_url',
        'subscription_plan',
        'subscription_status',
        'subscribed_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
