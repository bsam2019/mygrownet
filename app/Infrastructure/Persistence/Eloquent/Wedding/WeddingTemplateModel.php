<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeddingTemplateModel extends Model
{
    protected $table = 'wedding_templates';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image',
        'settings',
        'is_active',
        'is_premium',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
    ];

    public function weddingEvents(): HasMany
    {
        return $this->hasMany(WeddingEventModel::class, 'template_id');
    }
}
