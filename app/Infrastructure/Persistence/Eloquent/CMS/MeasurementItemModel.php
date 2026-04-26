<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MeasurementItemModel extends Model
{
    protected $table = 'cms_measurement_items';

    protected $fillable = [
        'company_id',
        'measurement_id',
        'location_name',
        'type',
        'width_top',
        'width_middle',
        'width_bottom',
        'height_left',
        'height_right',
        'final_width',
        'final_height',
        'area',
        'quantity',
        'total_area',
        'notes',
    ];

    protected $casts = [
        'width_top' => 'decimal:2',
        'width_middle' => 'decimal:2',
        'width_bottom' => 'decimal:2',
        'height_left' => 'decimal:2',
        'height_right' => 'decimal:2',
        'final_width' => 'decimal:2',
        'final_height' => 'decimal:2',
        'area' => 'decimal:4',
        'quantity' => 'integer',
        'total_area' => 'decimal:4',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(MeasurementRecordModel::class, 'measurement_id');
    }

    // Scopes
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // Auto-calculate dimensions before saving
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->calculateDimensions();
        });
    }

    /**
     * Calculate final dimensions and area
     */
    public function calculateDimensions(): void
    {
        // Final width = smallest width
        $this->final_width = min(
            $this->width_top,
            $this->width_middle,
            $this->width_bottom
        );

        // Final height = smallest height
        $this->final_height = min(
            $this->height_left,
            $this->height_right
        );

        // Area in m² (convert from mm to m²)
        $this->area = ($this->final_width * $this->final_height) / 1000000;

        // Total area = area × quantity
        $this->total_area = $this->area * $this->quantity;
    }

    /**
     * Get type label for display
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'sliding_window' => 'Sliding Window',
            'casement_window' => 'Casement Window',
            'sliding_door' => 'Sliding Door',
            'hinged_door' => 'Hinged Door',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get formatted dimensions (in metres)
     */
    public function getFormattedDimensions(): string
    {
        $widthM  = $this->final_width  / 1000;
        $heightM = $this->final_height / 1000;

        return sprintf(
            '%s m × %s m',
            rtrim(rtrim(number_format($widthM, 3), '0'), '.'),
            rtrim(rtrim(number_format($heightM, 3), '0'), '.')
        );
    }

    /**
     * Get formatted area
     */
    public function getFormattedArea(): string
    {
        return number_format($this->area, 2) . ' m²';
    }

    /**
     * Get formatted total area
     */
    public function getFormattedTotalArea(): string
    {
        return number_format($this->total_area, 2) . ' m²';
    }
}
