<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractorRatingModel extends Model
{
    protected $table = 'cms_subcontractor_ratings';

    protected $fillable = [
        'subcontractor_id', 'assignment_id', 'rated_by', 'quality_rating',
        'timeliness_rating', 'communication_rating', 'professionalism_rating',
        'overall_rating', 'comments',
    ];

    protected $casts = [
        'quality_rating' => 'integer',
        'timeliness_rating' => 'integer',
        'communication_rating' => 'integer',
        'professionalism_rating' => 'integer',
        'overall_rating' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($rating) {
            $rating->overall_rating = (
                $rating->quality_rating +
                $rating->timeliness_rating +
                $rating->communication_rating +
                $rating->professionalism_rating
            ) / 4;
        });
    }

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(SubcontractorModel::class, 'subcontractor_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(SubcontractorAssignmentModel::class, 'assignment_id');
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'rated_by');
    }
}
