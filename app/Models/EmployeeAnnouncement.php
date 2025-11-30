<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmployeeAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'department_id',
        'created_by',
        'publish_date',
        'expiry_date',
        'is_pinned',
        'is_active',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'expiry_date' => 'date',
        'is_pinned' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function readBy(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_announcement_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('publish_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }

    public function scopeForDepartment($query, ?int $departmentId)
    {
        return $query->where(function ($q) use ($departmentId) {
            $q->whereNull('department_id');
            if ($departmentId) {
                $q->orWhere('department_id', $departmentId);
            }
        });
    }

    public function isReadBy(int $employeeId): bool
    {
        return $this->readBy()->where('employee_id', $employeeId)->exists();
    }

    public function markAsReadBy(int $employeeId): void
    {
        if (!$this->isReadBy($employeeId)) {
            $this->readBy()->attach($employeeId, ['read_at' => now()]);
        }
    }
}
