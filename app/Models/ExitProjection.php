<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitProjection extends Model
{
    use HasFactory;

    protected $fillable = [
        'exit_type',
        'title',
        'description',
        'projected_date',
        'projected_valuation',
        'projected_multiple',
        'probability_percentage',
        'assumptions',
        'is_featured',
    ];

    protected $casts = [
        'projected_date' => 'date',
        'projected_valuation' => 'decimal:2',
        'projected_multiple' => 'decimal:2',
        'assumptions' => 'array',
        'is_featured' => 'boolean',
    ];

    public function getExitTypeLabel(): string
    {
        return match($this->exit_type) {
            'ipo' => 'Initial Public Offering (IPO)',
            'acquisition' => 'Acquisition',
            'buyback' => 'Share Buyback',
            'secondary_sale' => 'Secondary Sale',
            'dividend_recoup' => 'Dividend Recoupment',
            default => ucfirst($this->exit_type),
        };
    }
}
