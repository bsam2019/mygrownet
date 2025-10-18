<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_project_id',
        'created_by',
        'title',
        'content',
        'update_type',
        'attachments',
        'notify_contributors',
        'is_public'
    ];

    protected $casts = [
        'attachments' => 'array',
        'notify_contributors' => 'boolean',
        'is_public' => 'boolean'
    ];

    protected $attributes = [
        'attachments' => '[]'
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class, 'community_project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Business Logic Methods
    public static function getUpdateTypes(): array
    {
        return [
            'progress' => 'Progress Update',
            'milestone' => 'Milestone Achievement',
            'financial' => 'Financial Update',
            'announcement' => 'General Announcement',
            'issue' => 'Issue/Challenge'
        ];
    }
}