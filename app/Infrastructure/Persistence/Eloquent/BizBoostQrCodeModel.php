<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostQrCodeModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_qr_codes';

    protected $fillable = [
        'business_id',
        'name',
        'type',
        'target_url',
        'short_code',
        'qr_image_path',
        'style_config',
        'scan_count',
        'is_active',
    ];

    protected $casts = [
        'style_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
