<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'hours_worked',
        'overtime_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function clockIn(): void
    {
        $this->update(['clock_in' => now()->format('H:i:s')]);
    }

    public function clockOut(): void
    {
        $clockIn = \Carbon\Carbon::parse($this->clock_in);
        $clockOut = now();
        $breakMinutes = 0;

        if ($this->break_start && $this->break_end) {
            $breakStart = \Carbon\Carbon::parse($this->break_start);
            $breakEnd = \Carbon\Carbon::parse($this->break_end);
            $breakMinutes = $breakEnd->diffInMinutes($breakStart);
        }

        $totalMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;
        $hoursWorked = round($totalMinutes / 60, 2);
        $overtime = max(0, $hoursWorked - 8);

        $this->update([
            'clock_out' => $clockOut->format('H:i:s'),
            'hours_worked' => $hoursWorked,
            'overtime_hours' => $overtime,
        ]);
    }

    public static function todayFor(Employee $employee): ?self
    {
        return self::where('employee_id', $employee->id)
            ->where('date', today())
            ->first();
    }
}
