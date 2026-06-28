<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingStageModel extends Model
{
    protected $table = 'cms_billing_stages';

    protected $fillable = [
        'project_id', 'stage_name', 'description', 'percentage', 'amount',
        'order', 'status', 'target_date', 'completion_date',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'order' => 'integer',
        'target_date' => 'date',
        'completion_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }
}
