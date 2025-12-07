<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BizBoostPostMediaModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_post_media';

    protected $fillable = [
        'post_id',
        'type',
        'path',
        'filename',
        'file_size',
        'mime_type',
        'width',
        'height',
        'duration',
        'thumbnail_path',
        'sort_order',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BizBoostPostModel::class, 'post_id');
    }

    /**
     * Get the full URL for the media file.
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->path) {
            return Storage::disk('public')->url($this->path);
        }
        return null;
    }

    /**
     * Get the thumbnail URL for video files.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }
        return null;
    }
}
