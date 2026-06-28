<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureUpdateModel extends Model
{
    use SoftDeletes;

    protected $table = 'venture_updates';

    protected $fillable = [
        'venture_id',
        'title',
        'content',
        'type',
        'visibility',
        'send_notification',
        'is_pinned',
        'views_count',
        'posted_by',
        'published_at',
    ];

    protected $casts = [
        'send_notification' => 'boolean',
        'is_pinned' => 'boolean',
        'views_count' => 'integer',
        'published_at' => 'datetime',
    ];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(VentureModel::class, 'venture_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForInvestors($query)
    {
        return $query->whereIn('visibility', ['public', 'investors_only']);
    }

    public function scopeForShareholders($query)
    {
        return $query->whereIn('visibility', ['public', 'investors_only', 'shareholders_only']);
    }

    // Helpers
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    public function canBeViewedBy(string $userRole): bool
    {
        if (!$this->isPublished()) {
            return $userRole === 'admin';
        }

        return match($this->visibility) {
            'public' => true,
            'investors_only' => in_array($userRole, ['investor', 'shareholder', 'admin']),
            'shareholders_only' => in_array($userRole, ['shareholder', 'admin']),
            default => false,
        };
    }
}
