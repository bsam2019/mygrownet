<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBuilderPageView extends Model
{
    protected $table = 'growbuilder_page_views';

    protected $fillable = [
        'site_id',
        'page_id',
        'path',
        'referrer',
        'ip_address',
        'user_agent',
        'country',
        'device_type',
        'viewed_date',
    ];

    protected $casts = [
        'viewed_date' => 'date',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderPage::class, 'page_id');
    }
}
