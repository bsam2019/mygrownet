<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetentionTrackingModel extends Model
{
    protected $table = 'cms_retention_tracking';

    protected $fillable = [
        'project_id', 'progress_certificate_id', 'retention_amount', 'released_amount',
        'balance', 'release_due_date', 'actual_release_date', 'status', 'release_notes',
    ];

    protected $casts = [
        'retention_amount' => 'decimal:2',
        'released_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'release_due_date' => 'date',
        'actual_release_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($retention) {
            $retention->balance = $retention->retention_amount - $retention->released_amount;
            
            if ($retention->balance <= 0) {
                $retention->status = 'fully_released';
            } elseif ($retention->released_amount > 0) {
                $retention->status = 'partially_released';
            } else {
                $retention->status = 'held';
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function progressCertificate(): BelongsTo
    {
        return $this->belongsTo(ProgressCertificateModel::class, 'progress_certificate_id');
    }
}
