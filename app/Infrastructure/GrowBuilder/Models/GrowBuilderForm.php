<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBuilderForm extends Model
{
    protected $table = 'growbuilder_forms';

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'fields',
        'submit_button_text',
        'success_message',
        'notification_email',
        'send_whatsapp',
        'whatsapp_number',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'send_whatsapp' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(GrowBuilderFormSubmission::class, 'form_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
