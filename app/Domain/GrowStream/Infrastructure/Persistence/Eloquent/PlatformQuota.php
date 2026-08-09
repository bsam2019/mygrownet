<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformQuota extends Model
{
    use HasFactory;

    protected $table = 'growstream_platform_quotas';

    protected $fillable = [
        'organization_id',
        'storage_minutes_limit',
        'delivery_gb_limit',
        'current_storage_minutes',
        'current_delivery_gb',
    ];
}
