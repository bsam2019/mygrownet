<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuarterlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'venture_project_id',
        'quarter',
        'year',
        'title',
        'executive_summary',
        'financial_highlights',
        'operational_updates',
        'risk_factors',
        'outlook',
        'pdf_path',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'financial_highlights' => 'array',
        'operational_updates' => 'array',
        'risk_factors' => 'array',
        'published_at' => 'datetime',
    ];

    public function ventureProject(): BelongsTo
    {
        return $this->belongsTo(VentureProject::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function getQuarterNameAttribute(): string
    {
        return "Q{$this->quarter} {$this->year}";
    }

    public function isPublished(): bool
    {
        return !is_null($this->published_at);
    }

    public function getQuarterLabel(): string
    {
        return "Q{$this->quarter} {$this->year}";
    }
}