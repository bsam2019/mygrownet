<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Database\Factories\EmployeeClientAssignmentModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeClientAssignmentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return EmployeeClientAssignmentModelFactory::new();
    }

    protected $table = 'employee_client_assignments';

    protected $fillable = [
        'employee_id',
        'client_user_id',
        'assignment_type',
        'assigned_date',
        'unassigned_date',
        'status',
        'assignment_notes',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'unassigned_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'assigned_by');
    }

    // Scopes
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('client_user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('assignment_type', $type);
    }

    public function scopePrimary($query)
    {
        return $query->where('assignment_type', 'primary');
    }

    public function scopeSecondary($query)
    {
        return $query->where('assignment_type', 'secondary');
    }

    public function scopeSupport($query)
    {
        return $query->where('assignment_type', 'support');
    }

    public function scopeAssignedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('assigned_date', [$startDate, $endDate]);
    }

    public function scopeUnassignedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('unassigned_date', [$startDate, $endDate]);
    }

    public function scopeOrderByAssignedDate($query, string $direction = 'desc')
    {
        return $query->orderBy('assigned_date', $direction);
    }

    // Accessors
    public function getAssignmentTypeDisplayAttribute(): string
    {
        return match ($this->assignment_type) {
            'primary' => 'Primary Agent',
            'secondary' => 'Secondary Agent',
            'support' => 'Support Agent',
            default => ucfirst($this->assignment_type)
        };
    }

    public function getAssignmentDurationAttribute(): ?int
    {
        if (!$this->unassigned_date) {
            return $this->assigned_date->diffInDays(now());
        }
        
        return $this->assigned_date->diffInDays($this->unassigned_date);
    }

    public function getAssignmentDurationInMonthsAttribute(): ?int
    {
        if (!$this->unassigned_date) {
            return $this->assigned_date->diffInMonths(now());
        }
        
        return $this->assigned_date->diffInMonths($this->unassigned_date);
    }
}