<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MeasurementRecordModel extends Model
{
    protected $table = 'cms_measurement_records';

    protected $fillable = [
        'company_id',
        'customer_id',
        'measurement_number',
        'project_name',
        'location',
        'measured_by',
        'measurement_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'measurement_date' => 'date',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function measuredBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'measured_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MeasurementItemModel::class, 'measurement_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(QuotationModel::class, 'measurement_id');
    }

    // Scopes
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isQuoted(): bool
    {
        return $this->status === 'quoted';
    }

    public function getTotalArea(): float
    {
        return $this->items->sum('total_area');
    }

    public function getItemsCount(): int
    {
        return $this->items->count();
    }

    /**
     * Generate next measurement number for company
     */
    public static function generateMeasurementNumber(int $companyId): string
    {
        $lastMeasurement = static::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastMeasurement) {
            return 'MEAS-' . date('Y') . '-0001';
        }

        // Extract number from last measurement
        preg_match('/MEAS-\d{4}-(\d+)/', $lastMeasurement->measurement_number, $matches);
        $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
        $nextNumber = $lastNumber + 1;

        return 'MEAS-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
