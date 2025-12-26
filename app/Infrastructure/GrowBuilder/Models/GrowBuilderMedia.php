<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GrowBuilderMedia extends Model
{
    protected $table = 'growbuilder_media';

    protected $fillable = [
        'site_id',
        'filename',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
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

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getWebpUrlAttribute(): ?string
    {
        if (isset($this->variants['webp'])) {
            return Storage::disk($this->disk)->url($this->variants['webp']);
        }
        return null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (isset($this->variants['thumbnail'])) {
            return Storage::disk($this->disk)->url($this->variants['thumbnail']);
        }
        return $this->url;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
