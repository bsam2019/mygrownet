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

    protected $appends = [
        'aspect_ratio',
        'aspect_ratio_decimal',
        'file_type_badge',
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

    /**
     * Get aspect ratio as a human-readable string (e.g., "16:9", "3:2")
     */
    public function getAspectRatioAttribute(): string
    {
        if (!$this->width || !$this->height) {
            return 'Unknown';
        }
        
        $gcd = $this->gcd($this->width, $this->height);
        $ratioW = $this->width / $gcd;
        $ratioH = $this->height / $gcd;
        
        // Check for common aspect ratios
        $ratio = $ratioW / $ratioH;
        
        if (abs($ratio - 16/9) < 0.01) return '16:9';
        if (abs($ratio - 4/3) < 0.01) return '4:3';
        if (abs($ratio - 3/2) < 0.01) return '3:2';
        if (abs($ratio - 1) < 0.01) return '1:1';
        if (abs($ratio - 21/9) < 0.01) return '21:9';
        if (abs($ratio - 5/4) < 0.01) return '5:4';
        if (abs($ratio - 2.4) < 0.01) return '12:5'; // Common for hero sections
        
        // Return simplified ratio
        return round($ratioW, 1) . ':' . round($ratioH, 1);
    }

    /**
     * Get aspect ratio as decimal for calculations
     */
    public function getAspectRatioDecimalAttribute(): float
    {
        if (!$this->width || !$this->height) {
            return 0;
        }
        
        return round($this->width / $this->height, 2);
    }

    /**
     * Get file type badge color configuration
     */
    public function getFileTypeBadgeAttribute(): array
    {
        $extension = strtoupper(pathinfo($this->filename, PATHINFO_EXTENSION));
        
        $colors = [
            'JPG' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'JPG'],
            'JPEG' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'JPG'],
            'PNG' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'PNG'],
            'WEBP' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'WEBP'],
            'GIF' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'GIF'],
            'SVG' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'label' => 'SVG'],
        ];
        
        return $colors[$extension] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $extension];
    }

    /**
     * Calculate Greatest Common Divisor
     */
    private function gcd(int $a, int $b): int
    {
        return $b ? $this->gcd($b, $a % $b) : $a;
    }
}
