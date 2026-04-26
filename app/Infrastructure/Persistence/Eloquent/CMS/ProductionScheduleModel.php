<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionScheduleModel extends Model
{
    protected $table = 'cms_production_schedule';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'scheduled_date',
        'start_time',
        'end_time',
        'assigned_worker_id',
        'workstation',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function assignedWorker(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_worker_id');
    }
}
