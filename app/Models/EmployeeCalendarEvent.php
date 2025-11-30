<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'created_by',
        'title',
        'description',
        'type',
        'start_time',
        'end_time',
        'is_all_day',
        'location',
        'meeting_link',
        'status',
        'attendees',
        'reminders',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_all_day' => 'boolean',
        'attendees' => 'array',
        'reminders' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('start_time');
    }

    public function scopeForDateRange($query, $start, $end)
    {
        return $query->where('start_time', '>=', $start)
            ->where('start_time', '<=', $end);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_time', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }
}
