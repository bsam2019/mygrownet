<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkOperationModel extends Model
{
    protected $table = 'cms_bulk_operations';

    protected $fillable = [
        'company_id',
        'user_id',
        'operation_type',
        'tasks_affected',
        'operation_data',
        'task_ids',
        'executed_at',
    ];

    protected $casts = [
        'operation_data' => 'array',
        'task_ids' => 'array',
        'executed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
