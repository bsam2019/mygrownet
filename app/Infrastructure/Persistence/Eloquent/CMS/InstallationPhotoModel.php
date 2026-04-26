<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationPhotoModel extends Model
{
    protected $table = 'cms_installation_photos';

    protected $fillable = [
        'installation_schedule_id',
        'site_visit_id',
        'photo_type',
        'file_path',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
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
