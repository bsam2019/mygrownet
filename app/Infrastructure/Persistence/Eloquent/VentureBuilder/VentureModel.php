<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureModel extends Model
{
    use SoftDeletes;

    protected $table = 'ventures';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'business_model',
        'featured_image',
        'funding_target',
        'minimum_investment',
        'maximum_investment',
        'total_raised',
        'investor_count',
        'funding_start_date',
        'funding_end_date',
        'expected_launch_date',
        'actual_launch_date',
        'status',
        'company_name',
        'company_registration_number',
        'company_formation_date',
        'mygrownet_equity_percentage',
        'revenue_projections',
        'risk_factors',
        'expected_roi_months',
        'is_featured',
        'views_count',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'funding_target' => 'decimal:2',
        'minimum_investment' => 'decimal:2',
        'maximum_investment' => 'decimal:2',
        'total_raised' => 'decimal:2',
        'investor_count' => 'integer',
        'funding_start_date' => 'date',
        'funding_end_date' => 'date',
        'expected_launch_date' => 'date',
        'actual_launch_date' => 'date',
        'company_formation_date' => 'date',
        'mygrownet_equity_percentage' => 'decimal:2',
        'revenue_projections' => 'array',
        'expected_roi_months' => 'integer',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VentureCategoryModel::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function investments(): HasMany
    {
        return $this->hasMany(VentureInvestmentModel::class, 'venture_id');
    }

    public function shareholders(): HasMany
    {
        return $this->hasMany(VentureShareholderModel::class, 'venture_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VentureDocumentModel::class, 'venture_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(VentureUpdateModel::class, 'venture_id');
    }

    public function dividends(): HasMany
    {
        return $this->hasMany(VentureDividendModel::class, 'venture_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['funding', 'funded', 'active']);
    }

    public function scopeFunding($query)
    {
        return $query->where('status', 'funding');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helpers
    public function getFundingProgressPercentage(): float
    {
        if ($this->funding_target <= 0) {
            return 0;
        }
        return min(100, ($this->total_raised / $this->funding_target) * 100);
    }

    public function isFundingOpen(): bool
    {
        return $this->status === 'funding' 
            && (!$this->funding_end_date || $this->funding_end_date->isFuture());
    }

    public function canAcceptInvestments(): bool
    {
        return $this->isFundingOpen() 
            && $this->total_raised < $this->funding_target;
    }
}
