<?php

namespace App\Infrastructure\Persistence\Eloquent\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class PromotionalCardModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'promotional_cards';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'thumbnail_path',
        'category',
        'sort_order',
        'is_active',
        'og_title',
        'og_description',
        'og_image',
        'share_count',
        'view_count',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'share_count' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Get the creator of the card
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all shares for this card
     */
    public function shares(): HasMany
    {
        return $this->hasMany(PromotionalCardShareModel::class, 'promotional_card_id');
    }

    /**
     * Scope: Get only active cards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get cards by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Get the full OG image URL
     */
    public function getOgImageUrlAttribute(): string
    {
        $ogImage = $this->og_image ?? $this->image_path;
        return asset('storage/' . $ogImage);
    }

    /**
     * Get shares count for today
     */
    public function getTodaySharesCount(): int
    {
        return $this->shares()
            ->whereDate('shared_at', today())
            ->count();
    }
}
