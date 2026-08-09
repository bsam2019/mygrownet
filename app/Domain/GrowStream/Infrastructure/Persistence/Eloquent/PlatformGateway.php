<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PlatformGateway extends Model
{
    use HasFactory;

    protected $table = 'growstream_platform_gateways';

    protected $fillable = [
        'organization_id',
        'gateway_slug',
        'credentials_encrypted',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setCredentialsAttribute($value): void
    {
        $this->attributes['credentials_encrypted'] = Crypt::encryptString(json_encode($value));
    }

    public function getCredentialsAttribute(): ?array
    {
        if (empty($this->attributes['credentials_encrypted'])) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($this->attributes['credentials_encrypted']), true);
        } catch (\Exception $e) {
            return null;
        }
    }
}
