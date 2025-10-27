<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryResourceAccess extends Model
{
    use HasFactory;

    protected $table = 'library_resource_access';

    protected $fillable = [
        'user_id',
        'library_resource_id',
        'accessed_at',
        'time_spent_seconds',
        'completed',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(LibraryResource::class, 'library_resource_id');
    }
}
