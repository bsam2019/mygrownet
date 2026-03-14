<?php

namespace App\Models\QuickInvoice;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomTemplate extends Model
{
    protected $table = 'quick_invoice_custom_templates';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'base_template',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'heading_font',
        'header_style',
        'layout_style',
        'show_logo',
        'show_business_details',
        'logo_position',
        'logo_size',
        'border_radius',
        'border_style',
        'spacing',
        'table_style',
        'custom_css',
        'section_visibility',
        'field_labels',
        'layout_json',
        'field_config',
        'logo_url',
        'version',
        'category',
        'tags',
        'is_public',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_business_details' => 'boolean',
        'is_public' => 'boolean',
        'logo_size' => 'integer',
        'border_radius' => 'integer',
        'spacing' => 'integer',
        'usage_count' => 'integer',
        'version' => 'integer',
        'custom_css' => 'array',
        'section_visibility' => 'array',
        'field_labels' => 'array',
        'layout_json' => 'array',
        'field_config' => 'array',
        'tags' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Add computed properties
        $array['is_owner'] = auth()->check() && $this->user_id === auth()->id();
        $array['owner_name'] = $this->user->name ?? 'Unknown';
        
        return $array;
    }
}
