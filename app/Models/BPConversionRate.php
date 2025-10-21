<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BPConversionRate extends Model
{
    protected $table = 'bp_conversion_rates';

    protected $fillable = [
        'bp_to_kwacha_rate',
        'effective_from',
        'effective_to',
        'is_current',
        'notes',
    ];

    protected $casts = [
        'bp_to_kwacha_rate' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Get the current BP conversion rate
     */
    public static function getCurrentRate(): float
    {
        $rate = static::where('is_current', true)->first();
        return $rate?->bp_to_kwacha_rate ?? 0.50; // Default to K0.50 per BP
    }

    /**
     * Convert BP to Kwacha
     */
    public static function convertBPToKwacha(int $bp): float
    {
        return $bp * static::getCurrentRate();
    }

    /**
     * Convert Kwacha to BP
     */
    public static function convertKwachaToBP(float $kwacha): int
    {
        $rate = static::getCurrentRate();
        return $rate > 0 ? (int) round($kwacha / $rate) : 0;
    }

    /**
     * Set a new current rate (deactivates previous)
     */
    public static function setNewRate(float $rate, ?string $notes = null): self
    {
        // Deactivate all previous rates
        static::where('is_current', true)->update([
            'is_current' => false,
            'effective_to' => now(),
        ]);

        // Create new rate
        return static::create([
            'bp_to_kwacha_rate' => $rate,
            'effective_from' => now(),
            'is_current' => true,
            'notes' => $notes,
        ]);
    }

    /**
     * Get rate history
     */
    public static function getHistory()
    {
        return static::orderBy('effective_from', 'desc')->get();
    }
}
