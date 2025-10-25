<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'investment_id',
        'level',
        'amount',
        'percentage',
        'status',
        'paid_at',
        'team_volume',
        'personal_volume',
        'commission_type',
        'package_type',
        'package_amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'team_volume' => 'decimal:2',
        'personal_volume' => 'decimal:2',
        'package_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // MyGrowNet 7-level professional progression commission rates
    public const COMMISSION_RATES = [
        1 => 15.0, // Level 1 (Associate): 15%
        2 => 10.0, // Level 2 (Professional): 10%
        3 => 8.0,  // Level 3 (Senior): 8%
        4 => 6.0,  // Level 4 (Manager): 6%
        5 => 4.0,  // Level 5 (Director): 4%
        6 => 3.0,  // Level 6 (Executive): 3%
        7 => 2.0,  // Level 7 (Ambassador): 2%
    ];
    
    // Professional level names for the 7-level system
    public const LEVEL_NAMES = [
        1 => 'Associate',
        2 => 'Professional',
        3 => 'Senior',
        4 => 'Manager',
        5 => 'Director',
        6 => 'Executive',
        7 => 'Ambassador',
    ];
    
    // Maximum commission depth
    public const MAX_COMMISSION_LEVELS = 7;

    public const COMMISSION_TYPES = [
        'REFERRAL' => 'REFERRAL',
        'TEAM_VOLUME' => 'TEAM_VOLUME',
        'PERFORMANCE' => 'PERFORMANCE'
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Get commission rate for specific level
     */
    public static function getCommissionRate(int $level): float
    {
        return self::COMMISSION_RATES[$level] ?? 0.0;
    }
    
    /**
     * Get professional level name for a specific level
     */
    public static function getLevelName(int $level): string
    {
        return self::LEVEL_NAMES[$level] ?? "Level {$level}";
    }
    
    /**
     * Get all commission rates with level names
     */
    public static function getAllCommissionRates(): array
    {
        $rates = [];
        foreach (self::COMMISSION_RATES as $level => $rate) {
            $rates[$level] = [
                'level' => $level,
                'name' => self::getLevelName($level),
                'rate' => $rate,
            ];
        }
        return $rates;
    }

    /**
     * Calculate commission amount based on package purchase and level
     */
    public static function calculateCommissionAmount(float $packageAmount, int $level): float
    {
        $rate = self::getCommissionRate($level);
        return $packageAmount * ($rate / 100);
    }

    /**
     * Check if commission is eligible for payment
     */
    public function isEligibleForPayment(): bool
    {
        if ($this->status !== 'pending' || !$this->referrer) {
            return false;
        }
        
        // For registration commissions, only check if referrer is active
        if ($this->package_type === 'registration' || $this->package_type === 'wallet_topup') {
            return $this->referrer->status === 'active';
        }
        
        // For other commission types, check active subscription
        return $this->referrer->hasActiveSubscription();
    }

    /**
     * Scope for specific commission types
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('commission_type', $type);
    }

    /**
     * Scope for specific levels
     */
    public function scopeLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for paid commissions
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for pending commissions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for commissions within date range
     */
    public function scopeWithinPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for team volume commissions
     */
    public function scopeTeamVolume($query)
    {
        return $query->where('commission_type', 'TEAM_VOLUME');
    }

    /**
     * Scope for performance commissions
     */
    public function scopePerformance($query)
    {
        return $query->where('commission_type', 'PERFORMANCE');
    }

    /**
     * Calculate total commissions for a user across all levels
     */
    public static function calculateTotalCommissions(int $userId, string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $commissions = self::where('referrer_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                level,
                commission_type,
                SUM(amount) as total_amount,
                COUNT(*) as count
            ')
            ->groupBy(['level', 'commission_type'])
            ->get();

        $result = [
            'total_amount' => $commissions->sum('total_amount'),
            'by_level' => [],
            'by_type' => []
        ];

        foreach ($commissions as $commission) {
            $result['by_level'][$commission->level] = [
                'amount' => $commission->total_amount,
                'count' => $commission->count
            ];
            
            if (!isset($result['by_type'][$commission->commission_type])) {
                $result['by_type'][$commission->commission_type] = [
                    'amount' => 0,
                    'count' => 0
                ];
            }
            
            $result['by_type'][$commission->commission_type]['amount'] += $commission->total_amount;
            $result['by_type'][$commission->commission_type]['count'] += $commission->count;
        }

        return $result;
    }

    /**
     * Process commission payment
     * Converts commission amount to BP (Bonus Points) at K2 per point
     */
    public function processPayment(): bool
    {
        if (!$this->isEligibleForPayment()) {
            return false;
        }

        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        // Convert commission to BP (Bonus Points)
        // Value per BP: K2, so BP = Amount ÷ 2
        $bpAmount = $this->amount / 2;

        // Award BP to referrer using PointService
        $pointService = app(\App\Services\PointService::class);
        $pointService->awardPoints(
            user: $this->referrer,
            source: 'referral_commission',
            lpAmount: 0, // No LP for commissions
            mapAmount: (int) round($bpAmount), // Convert to BP (MAP = Monthly Activity Points = BP)
            description: "Level {$this->level} referral commission: K{$this->amount} = {$bpAmount} BP",
            reference: $this
        );
        
        // Record activity
        $this->referrer->recordActivity(
            'commission_received',
            "Received {$bpAmount} BP (K{$this->amount} value) - Level {$this->level} {$this->commission_type} commission"
        );

        return true;
    }
}