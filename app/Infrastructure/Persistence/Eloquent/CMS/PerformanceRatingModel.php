<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceRatingModel extends Model
{
    protected $table = 'cms_performance_ratings';

    protected $fillable = [
        'review_id',
        'criteria_id',
        'rating',
        'comments',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewModel::class, 'review_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(PerformanceCriteriaModel::class, 'criteria_id');
    }
}
