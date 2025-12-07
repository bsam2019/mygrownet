<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Models\User;

class GrowFinanceInvoiceTemplateModel extends Model
{
    protected $table = 'growfinance_invoice_templates';

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'layout',
        'colors',
        'fonts',
        'logo_position',
        'show_logo',
        'show_watermark',
        'header_text',
        'footer_text',
        'terms_text',
        'custom_fields',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'colors' => 'array',
        'fonts' => 'array',
        'custom_fields' => 'array',
        'show_logo' => 'boolean',
        'show_watermark' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Available layouts
    public const LAYOUT_STANDARD = 'standard';
    public const LAYOUT_MODERN = 'modern';
    public const LAYOUT_MINIMAL = 'minimal';
    public const LAYOUT_PROFESSIONAL = 'professional';

    public const LAYOUTS = [
        self::LAYOUT_STANDARD => 'Standard',
        self::LAYOUT_MODERN => 'Modern',
        self::LAYOUT_MINIMAL => 'Minimal',
        self::LAYOUT_PROFESSIONAL => 'Professional',
    ];

    // Default colors
    public const DEFAULT_COLORS = [
        'primary' => '#2563eb',
        'secondary' => '#64748b',
        'accent' => '#059669',
        'text' => '#1f2937',
        'background' => '#ffffff',
    ];

    // Default fonts
    public const DEFAULT_FONTS = [
        'heading' => 'Inter',
        'body' => 'Inter',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
            if (empty($template->colors)) {
                $template->colors = self::DEFAULT_COLORS;
            }
            if (empty($template->fonts)) {
                $template->fonts = self::DEFAULT_FONTS;
            }
        });

        // Ensure only one default template per business
        static::saving(function ($template) {
            if ($template->is_default) {
                static::where('business_id', $template->business_id)
                    ->where('id', '!=', $template->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getColor(string $key): string
    {
        return $this->colors[$key] ?? self::DEFAULT_COLORS[$key] ?? '#000000';
    }

    public function getFont(string $key): string
    {
        return $this->fonts[$key] ?? self::DEFAULT_FONTS[$key] ?? 'Inter';
    }
}
