<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubcontractorModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_subcontractors';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'trade',
        'specialization',
        'rating',
        'completed_jobs',
        'status',
        'tax_number',
        'registration_number',
        'insurance_expiry',
        'certifications',
        'notes',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'completed_jobs' => 'integer',
        'insurance_expiry' => 'date',
        'certifications' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SubcontractorAssignmentModel::class, 'subcontractor_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubcontractorPaymentModel::class, 'subcontractor_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(SubcontractorRatingModel::class, 'subcontractor_id');
    }

    public function updateRating(): void
    {
        $avgRating = $this->ratings()->avg('overall_rating');
        $this->rating = $avgRating ?? 0;
        $this->save();
    }

    public function isInsuranceExpired(): bool
    {
        return $this->insurance_expiry && now()->gt($this->insurance_expiry);
    }
}
