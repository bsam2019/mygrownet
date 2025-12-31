<?php

namespace App\Models\GrowBuilder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteTemplatePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_template_id',
        'title',
        'slug',
        'is_homepage',
        'show_in_nav',
        'content',
        'sort_order',
    ];

    protected $casts = [
        'is_homepage' => 'boolean',
        'show_in_nav' => 'boolean',
        'content' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SiteTemplate::class, 'site_template_id');
    }
}
