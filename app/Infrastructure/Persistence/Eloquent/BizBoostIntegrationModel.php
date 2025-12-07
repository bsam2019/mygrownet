<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostIntegrationModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_integrations';

    protected $fillable = [
        'business_id',
        'provider',
        'provider_user_id',
        'provider_page_id',
        'provider_page_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'meta',
        'status',
        'connected_at',
        'last_used_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
        'last_used_at' => 'datetime',
        'scopes' => 'array',
        'meta' => 'array',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
