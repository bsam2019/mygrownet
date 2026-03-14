<?php

namespace App\Models\QuickInvoice;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageTracking extends Model
{
    use HasUuids;

    protected $table = 'quick_invoice_usage_tracking';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'document_type',
        'template_used',
        'integration_source',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Track document creation
     */
    public static function track(
        string $documentType,
        string $template,
        ?int $userId = null,
        ?string $sessionId = null,
        string $integrationSource = 'standalone'
    ): void {
        self::create([
            'user_id' => $userId,
            'session_id' => $sessionId ?? session()->getId(),
            'document_type' => $documentType,
            'template_used' => $template,
            'integration_source' => $integrationSource,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get usage stats for a date range
     */
    public static function getStats(string $startDate, string $endDate): array
    {
        $query = self::whereBetween('created_at', [$startDate, $endDate]);
        
        return [
            'total_documents' => $query->count(),
            'unique_users' => $query->whereNotNull('user_id')->distinct('user_id')->count(),
            'unique_sessions' => $query->whereNotNull('session_id')->distinct('session_id')->count(),
            'by_type' => $query->groupBy('document_type')
                ->selectRaw('document_type, COUNT(*) as count')
                ->pluck('count', 'document_type')
                ->toArray(),
            'by_template' => $query->groupBy('template_used')
                ->selectRaw('template_used, COUNT(*) as count')
                ->pluck('count', 'template_used')
                ->toArray(),
            'by_source' => $query->groupBy('integration_source')
                ->selectRaw('integration_source, COUNT(*) as count')
                ->pluck('count', 'integration_source')
                ->toArray(),
        ];
    }

    /**
     * Get user's monthly usage
     */
    public static function getUserMonthlyUsage(int $userId, ?string $month = null): int
    {
        $month = $month ?? now()->format('Y-m');
        
        return self::where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
            ->count();
    }
}