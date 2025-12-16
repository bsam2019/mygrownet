<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusNoteModel extends Model
{
    protected $table = 'lifeplus_notes';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_pinned',
        'is_synced',
        'local_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_synced' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
