<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerComplaintModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_customer_complaints';

    protected $fillable = [
        'company_id',
        'complaint_number',
        'complaint_date',
        'customer_id',
        'job_id',
        'complaint_type',
        'priority',
        'description',
        'customer_expectation',
        'assigned_to',
        'resolution',
        'resolved_date',
        'status',
    ];

    protected $casts = [
        'complaint_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
