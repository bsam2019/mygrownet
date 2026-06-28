<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BgfEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'evaluator_id',
        'membership_score',
        'training_score',
        'viability_score',
        'credibility_score',
        'contribution_score',
        'risk_control_score',
        'track_record_score',
        'total_score',
        'recommendation',
        'membership_notes',
        'training_notes',
        'viability_notes',
        'credibility_notes',
        'contribution_notes',
        'risk_control_notes',
        'track_record_notes',
        'overall_notes',
        'risk_level',
        'risk_factors',
        'mitigation_suggestions',
    ];

    protected $casts = [
        'risk_factors' => 'array',
        'mitigation_suggestions' => 'array',
    ];

    // Relationships
    public function application(): BelongsTo
    {
        return $this->belongsTo(BgfApplication::class, 'application_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Helper Methods
    public function calculateTotalScore(): int
    {
        return $this->membership_score +
               $this->training_score +
               $this->viability_score +
               $this->credibility_score +
               $this->contribution_score +
               $this->risk_control_score +
               $this->track_record_score;
    }

    public function isPassingScore(): bool
    {
        return $this->total_score >= 70;
    }

    public function getScoreBreakdown(): array
    {
        return [
            'Membership & Activity' => ['score' => $this->membership_score, 'weight' => 15],
            'Training Completion' => ['score' => $this->training_score, 'weight' => 10],
            'Business Viability' => ['score' => $this->viability_score, 'weight' => 25],
            'Credibility & References' => ['score' => $this->credibility_score, 'weight' => 15],
            'Member Contribution' => ['score' => $this->contribution_score, 'weight' => 15],
            'Risk Control' => ['score' => $this->risk_control_score, 'weight' => 10],
            'Track Record' => ['score' => $this->track_record_score, 'weight' => 10],
        ];
    }
}
