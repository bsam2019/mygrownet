<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBizEmployeeModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_employees';

    protected $fillable = [
        'manager_id',
        'user_id',
        'supervisor_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'department',
        'status',
        'hire_date',
        'hourly_rate',
        'notes',
    ];

    protected $casts = [
        'manager_id' => 'integer',
        'user_id' => 'integer',
        'supervisor_id' => 'integer',
        'hire_date' => 'date',
        'hourly_rate' => 'decimal:2',
    ];

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(GrowBizTaskAssignmentModel::class, 'employee_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForManager($query, int $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeInDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get the supervisor of this employee
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    /**
     * Get employees who report to this employee (direct reports)
     */
    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    /**
     * Check if this employee has any direct reports
     */
    public function hasSupervisorRole(): bool
    {
        return $this->directReports()->exists();
    }

    /**
     * Get all subordinates recursively (direct reports and their reports)
     */
    public function getAllSubordinateIds(): array
    {
        $ids = [];
        $directReports = $this->directReports()->get();
        
        foreach ($directReports as $report) {
            $ids[] = $report->id;
            $ids = array_merge($ids, $report->getAllSubordinateIds());
        }
        
        return $ids;
    }
}
