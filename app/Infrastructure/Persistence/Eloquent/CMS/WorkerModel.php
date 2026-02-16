<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerModel extends Model
{
    protected $table = 'cms_workers';

    protected $fillable = [
        'company_id', 'worker_number', 'name', 'phone', 'email', 'id_number',
        'worker_type', 'hourly_rate', 'daily_rate', 'commission_rate',
        'payment_method', 'mobile_money_number', 'bank_name', 'bank_account_number',
        'status', 'notes', 'created_by',
        // HRMS fields
        'first_name', 'last_name', 'middle_name',
        'date_of_birth', 'gender', 'nationality',
        'address', 'city', 'province',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'job_title', 'department_id', 'hire_date',
        'employment_type', 'contract_start_date', 'contract_end_date', 'probation_end_date',
        'monthly_salary', 'salary_currency',
        'tax_number', 'napsa_number', 'nhima_number',
        'photo_path', 'documents',
        'employment_status', 'termination_date', 'termination_reason',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'termination_date' => 'date',
        'documents' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(WorkerAttendanceModel::class, 'worker_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(CommissionModel::class, 'worker_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalanceModel::class, 'worker_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequestModel::class, 'worker_id');
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(WorkerAllowanceModel::class, 'worker_id');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(WorkerDeductionModel::class, 'worker_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecordModel::class, 'worker_id');
    }

    public function overtimeRecords(): HasMany
    {
        return $this->hasMany(OvertimeRecordModel::class, 'worker_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
