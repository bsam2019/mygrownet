<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerShiftModel extends Model
{
    protected $table = 'cms_worker_shifts';

    protected $fillable = [
        'company_id',
        'worker_id',
        'shift_id',
        'effective_from',
        'effective_to',
        'days_of_week',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftModel::class, 'shift_id');
    }

    /**
     * Check if shift is active on a given date
     */
    public function isActiveOnDate(\Carbon\Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check date range
        if ($date->lessThan($this->effective_from)) {
            return false;
        }

        if ($this->effective_to && $date->greaterThan($this->effective_to)) {
            return false;
        }

        // Check day of week if specified
        if ($this->days_of_week) {
            $dayOfWeek = $date->dayOfWeekIso; // 1 = Monday, 7 = Sunday
            return in_array($dayOfWeek, $this->days_of_week);
        }

        return true;
    }

    /**
     * Scope to get active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get assignments for a specific date
     */
    public function scopeForDate($query, \Carbon\Carbon $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }
}
