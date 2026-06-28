<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplicationModel extends Model
{
    protected $table = 'cms_job_applications';

    protected $fillable = [
        'job_posting_id',
        'applicant_name',
        'email',
        'phone',
        'cv_path',
        'cover_letter',
        'status',
    ];

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPostingModel::class, 'job_posting_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(InterviewModel::class, 'application_id');
    }

    public function offerLetter(): HasOne
    {
        return $this->hasOne(OfferLetterModel::class, 'application_id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
