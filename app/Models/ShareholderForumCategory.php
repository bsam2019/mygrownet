<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareholderForumCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
        'requires_moderation',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_moderation' => 'boolean',
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(ShareholderForumTopic::class, 'category_id');
    }

    public function approvedTopics(): HasMany
    {
        return $this->topics()->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getTopicsCount(): int
    {
        return $this->approvedTopics()->count();
    }
}
