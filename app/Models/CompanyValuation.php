<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'valuation_amount',
        'valuation_date',
        'valuation_method',
        'notes',
        'assumptions',
        'created_by_user_id',
    ];

    protected $casts = [
        'valuation_amount' => 'decimal:2',
        'valuation_date' => 'date',
        'assumptions' => 'array',
    ];

    public static function getLatest(): ?self
    {
        return static::orderBy('valuation_date', 'desc')
            ->first();
    }

    public static function getHistory(int $months = 24): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('valuation_date', '>=', now()->subMonths($months))
            ->orderBy('valuation_date', 'asc')
            ->get();
    }
}
