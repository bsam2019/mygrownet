<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class InvestorAnnouncementModel extends Model
{
    protected $table = 'investor_announcements';

    protected $fillable = [
        'title',
        'content',
        'summary',
        'type',
        'priority',
        'is_pinned',
        'send_email',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'send_email' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readByInvestors(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::class,
            'investor_announcement_reads',
            'announcement_id',
            'investor_account_id'
        )->withPivot('read_at');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeActive($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')");
    }
}
