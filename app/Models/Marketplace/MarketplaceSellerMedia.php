<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MarketplaceSellerMedia extends Model
{
    protected $table = 'marketplace_seller_media';

    protected $fillable = [
        'seller_id',
        'filename',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'variants',
        'metadata',
    ];

    protected $casts = [
        'variants' => 'array',
        'metadata' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (isset($this->variants['thumbnail'])) {
            return Storage::disk($this->disk)->url($this->variants['thumbnail']);
        }
        return $this->url;
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
