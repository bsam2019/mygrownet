<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'description',
        'content',
        'content_type',
        'video_url',
        'document_url',
        'duration_minutes',
        'order',
        'is_preview',
        'required_tier_level',
        'status'
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'duration_minutes' => 'integer'
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    // Business Logic
    public function isAccessibleByTier(string $tierName): bool
    {
        if (!$this->required_tier_level) {
            return true;
        }

        $tierHierarchy = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'];
        $requiredIndex = array_search($this->required_tier_level, $tierHierarchy);
        $userIndex = array_search($tierName, $tierHierarchy);

        return $userIndex !== false && $userIndex >= $requiredIndex;
    }

    public function isCompletedByUser(User $user): bool
    {
        return $this->completions()
                    ->where('user_id', $user->id)
                    ->where('completed_at', '!=', null)
                    ->exists();
    }
}