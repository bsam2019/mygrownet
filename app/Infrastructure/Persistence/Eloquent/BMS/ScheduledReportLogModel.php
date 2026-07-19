<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledReportLogModel extends Model
{
    protected $table = 'cms_scheduled_report_logs';

    protected $fillable = [
        'scheduled_report_id',
        'status',
        'error_message',
        'recipients_count',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function scheduledReport(): BelongsTo
    {
        return $this->belongsTo(ScheduledReportModel::class, 'scheduled_report_id');
    }
}
