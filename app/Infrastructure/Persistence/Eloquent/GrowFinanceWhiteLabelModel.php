<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceWhiteLabelModel extends Model
{
    protected $table = 'growfinance_white_label_settings';

    protected $fillable = [
        'business_id',
        'company_name',
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'custom_domain',
        'hide_powered_by',
        'custom_css',
        'email_branding',
    ];

    protected $casts = [
        'hide_powered_by' => 'boolean',
        'email_branding' => 'array',
    ];

    // Default branding
    public const DEFAULT_PRIMARY_COLOR = '#2563eb';
    public const DEFAULT_SECONDARY_COLOR = '#1e40af';
    public const DEFAULT_ACCENT_COLOR = '#059669';

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function getLogoUrl(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        return asset('storage/' . $this->logo_path);
    }

    public function getFaviconUrl(): ?string
    {
        if (!$this->favicon_path) {
            return null;
        }
        return asset('storage/' . $this->favicon_path);
    }

    public function getDisplayName(): string
    {
        return $this->company_name ?? 'GrowFinance';
    }

    public function getCssVariables(): string
    {
        return <<<CSS
:root {
    --gf-primary: {$this->primary_color};
    --gf-secondary: {$this->secondary_color};
    --gf-accent: {$this->accent_color};
}
CSS;
    }
}
