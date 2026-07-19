<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyInspectionModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_safety_inspections';

    protected $fillable = [
        'company_id',
        'inspection_date',
        'inspection_type',
        'location',
        'inspected_by',
        'findings',
        'recommendations',
        'status',
        'follow_up_date',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'follow_up_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }
}
