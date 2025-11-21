<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPlanExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_plan_id',
        'user_id',
        'export_type',
        'file_path',
        'download_count',
        'last_downloaded_at',
    ];

    protected $casts = [
        'download_count' => 'integer',
        'last_downloaded_at' => 'datetime',
    ];

    public function businessPlan(): BelongsTo
    {
        return $this->belongsTo(BusinessPlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
