<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestorSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'questions',
        'start_date',
        'end_date',
        'status',
        'is_anonymous',
    ];

    protected $casts = [
        'questions' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(InvestorSurveyResponse::class, 'survey_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && now()->between($this->start_date, $this->end_date);
    }
}
