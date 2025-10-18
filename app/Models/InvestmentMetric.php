<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InvestmentMetric extends Model
{
    protected $fillable = [
        'date',
        'total_value',
        'total_count',
        'average_roi',
        'active_investors',
        'success_rate'
    ];

    protected $casts = [
        'date' => 'date',
        'total_value' => 'decimal:2',
        'average_roi' => 'decimal:2',
        'success_rate' => 'decimal:2'
    ];

    public static function recordDailyMetrics(): self
    {
        $today = now()->toDateString();

        return static::updateOrCreate(
            ['date' => $today],
            [
                'total_value' => Investment::sum('amount'),
                'total_count' => Investment::where('status', 'active')->count(),
                'average_roi' => Investment::where('status', 'active')->avg('roi') ?? 0,
                'active_investors' => User::whereHas('investments', function($query) {
                    $query->where('status', 'active');
                })->count(),
                'success_rate' => static::calculateSuccessRate()
            ]
        );
    }

    private static function calculateSuccessRate(): float
    {
        $total = Investment::count();
        if ($total === 0) return 0;

        $successful = Investment::whereIn('status', ['completed', 'active'])->count();
        return round(($successful / $total) * 100, 2);
    }
}
