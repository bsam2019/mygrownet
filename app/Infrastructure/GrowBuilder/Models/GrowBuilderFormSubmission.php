<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBuilderFormSubmission extends Model
{
    protected $table = 'growbuilder_form_submissions';

    protected $fillable = [
        'form_id',
        'site_id',
        'data',
        'ip_address',
        'user_agent',
        'is_read',
        'is_spam',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_spam' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderForm::class, 'form_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeNotSpam($query)
    {
        return $query->where('is_spam', false);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function markAsSpam(): void
    {
        $this->update(['is_spam' => true]);
    }
}
