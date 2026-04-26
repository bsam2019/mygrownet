<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSignoffModel extends Model
{
    protected $table = 'cms_customer_signoffs';

    protected $fillable = [
        'installation_schedule_id',
        'site_visit_id',
        'signoff_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'signature_data',
        'satisfaction_rating',
        'feedback',
    ];

    protected $casts = [
        'signoff_date' => 'date',
        'satisfaction_rating' => 'integer',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(InstallationScheduleModel::class, 'installation_schedule_id');
    }

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisitModel::class, 'site_visit_id');
    }
}
