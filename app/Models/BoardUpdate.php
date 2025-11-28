<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'venture_project_id',
        'title',
        'content',
        'update_type',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function ventureProject(): BelongsTo
    {und(): BelongsTo
    {
        return $this->belongsTo(InvestmentRound::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function isPublished(): bool
    {
        return !is_null($this->published_at);
    }
}

        return $this->belongsTo(VentureProject::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function isPublished(): bool
    {
        return !is_null($this->published_at);
    }
}
