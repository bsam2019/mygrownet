<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPostingModel extends Model
{
    protected $table = 'cms_job_postings';

    protected $fillable = [
        'company_id',
        'job_title',
        'department_id',
        'job_description',
        'requirements',
        'salary_range_min',
        'salary_range_max',
        'positions_available',
        'application_deadline',
        'status',
        'created_by',
    ];

    protected $casts = [
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
        'positions_available' => 'integer',
        'application_deadline' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplicationModel::class, 'job_posting_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', now());
            });
    }
}
