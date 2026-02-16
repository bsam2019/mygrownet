<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewEvaluationModel extends Model
{
    protected $table = 'cms_interview_evaluations';

    protected $fillable = [
        'interview_id',
        'evaluator_id',
        'technical_skills_rating',
        'communication_rating',
        'cultural_fit_rating',
        'overall_rating',
        'comments',
        'recommendation',
    ];

    protected $casts = [
        'technical_skills_rating' => 'integer',
        'communication_rating' => 'integer',
        'cultural_fit_rating' => 'integer',
        'overall_rating' => 'integer',
    ];

    public function interview(): BelongsTo
    {
        return $this->belongsTo(InterviewModel::class, 'interview_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'evaluator_id');
    }
}
