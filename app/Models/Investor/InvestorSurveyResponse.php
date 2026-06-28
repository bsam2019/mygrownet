<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorSurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'investor_account_id',
        'answers',
        'submitted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(InvestorSurvey::class, 'survey_id');
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }
}
