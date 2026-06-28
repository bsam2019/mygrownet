<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BizBoostProductImageModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_product_images';

    protected $fillable = ['product_id', 'path', 'filename', 'file_size', 'is_primary', 'sort_order'];

    protected $casts = ['is_primary' => 'boolean'];

    protected $appends = ['url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(BizBoostProductModel::class, 'product_id');
    }

    /**
     * Get the full URL for the image.
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->path) {
            return Storage::disk('public')->url($this->path);
        }
        return null;
    }
}
