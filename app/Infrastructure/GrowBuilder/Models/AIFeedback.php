<?php

namespace App\Infrastructure\GrowBuilder\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIFeedback extends Model
{
    protected $table = 'growbuilder_ai_feedback';

    protected $fillable = [
        'site_id',
        'user_id',
        'action_type',
        'section_type',
        'business_type', // Added for global learning by industry
        'applied',
        'user_message',
        'ai_response',
        'context',
    ];

    protected $casts = [
        'applied' => 'boolean',
        'context' => 'array',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get feedback statistics for a specific site
     */
    public static function getStatsForSite(int $siteId): array
    {
        $feedback = self::where('site_id', $siteId)
            ->selectRaw('action_type, section_type, applied, COUNT(*) as count')
            ->groupBy('action_type', 'section_type', 'applied')
            ->get();

        return self::calculateStats($feedback);
    }

    /**
     * Get GLOBAL feedback statistics across ALL sites
     * This helps new users benefit from everyone's learning
     */
    public static function getGlobalStats(): array
    {
        $feedback = self::selectRaw('action_type, section_type, applied, COUNT(*) as count')
            ->groupBy('action_type', 'section_type', 'applied')
            ->get();

        return self::calculateStats($feedback);
    }

    /**
     * Get feedback statistics by business type (industry-specific learning)
     * e.g., What works best for restaurants vs churches
     */
    public static function getStatsByBusinessType(string $businessType): array
    {
        $feedback = self::where('business_type', $businessType)
            ->selectRaw('action_type, section_type, applied, COUNT(*) as count')
            ->groupBy('action_type', 'section_type', 'applied')
            ->get();

        return self::calculateStats($feedback);
    }

    /**
     * Get combined stats: site-specific + industry + global
     * Weighted: site (50%) + industry (30%) + global (20%)
     */
    public static function getCombinedStats(int $siteId, ?string $businessType = null): array
    {
        $siteStats = self::getStatsForSite($siteId);
        $globalStats = self::getGlobalStats();
        $industryStats = $businessType ? self::getStatsByBusinessType($businessType) : null;

        // If site has enough data, use it primarily
        if ($siteStats['total'] >= 10) {
            return [
                'primary' => $siteStats,
                'industry' => $industryStats,
                'global' => $globalStats,
                'source' => 'site',
                'confidence' => 'high',
            ];
        }

        // If industry has data, use it as primary
        if ($industryStats && $industryStats['total'] >= 20) {
            return [
                'primary' => $industryStats,
                'site' => $siteStats,
                'global' => $globalStats,
                'source' => 'industry',
                'confidence' => 'medium',
            ];
        }

        // Fall back to global stats
        return [
            'primary' => $globalStats,
            'site' => $siteStats,
            'industry' => $industryStats,
            'source' => 'global',
            'confidence' => $globalStats['total'] >= 50 ? 'medium' : 'low',
        ];
    }

    /**
     * Calculate stats from feedback collection
     */
    private static function calculateStats($feedback): array
    {
        $stats = [
            'total' => 0,
            'applied' => 0,
            'rejected' => 0,
            'acceptance_rate' => 0,
            'by_action_type' => [],
            'by_section_type' => [],
            'preferred_actions' => [],
            'avoided_actions' => [],
        ];

        foreach ($feedback as $row) {
            $stats['total'] += $row->count;
            
            if ($row->applied) {
                $stats['applied'] += $row->count;
            } else {
                $stats['rejected'] += $row->count;
            }

            // Track by action type
            if (!isset($stats['by_action_type'][$row->action_type])) {
                $stats['by_action_type'][$row->action_type] = ['applied' => 0, 'rejected' => 0];
            }
            $stats['by_action_type'][$row->action_type][$row->applied ? 'applied' : 'rejected'] += $row->count;

            // Track by section type
            if ($row->section_type) {
                if (!isset($stats['by_section_type'][$row->section_type])) {
                    $stats['by_section_type'][$row->section_type] = ['applied' => 0, 'rejected' => 0];
                }
                $stats['by_section_type'][$row->section_type][$row->applied ? 'applied' : 'rejected'] += $row->count;
            }
        }

        // Calculate acceptance rate
        if ($stats['total'] > 0) {
            $stats['acceptance_rate'] = round(($stats['applied'] / $stats['total']) * 100);
        }

        // Find preferred actions (high acceptance rate with enough data)
        foreach ($stats['by_action_type'] as $type => $data) {
            $total = $data['applied'] + $data['rejected'];
            if ($total >= 3) {
                $rate = round(($data['applied'] / $total) * 100);
                if ($rate >= 70) {
                    $stats['preferred_actions'][] = $type;
                } elseif ($rate <= 30) {
                    $stats['avoided_actions'][] = $type;
                }
            }
        }

        return $stats;
    }

    /**
     * Get recent feedback for context (site-specific)
     */
    public static function getRecentForSite(int $siteId, int $limit = 20): array
    {
        return self::where('site_id', $siteId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($f) => [
                'type' => $f->action_type,
                'section' => $f->section_type,
                'applied' => $f->applied,
                'timestamp' => $f->created_at->toISOString(),
            ])
            ->toArray();
    }

    /**
     * Get top performing content types globally
     * Useful for suggesting what works best across all sites
     */
    public static function getTopPerformingContent(int $limit = 5): array
    {
        return self::where('applied', true)
            ->selectRaw('section_type, COUNT(*) as count')
            ->whereNotNull('section_type')
            ->groupBy('section_type')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('section_type')
            ->toArray();
    }

    /**
     * Get insights for AI prompt enhancement
     */
    public static function getInsightsForAI(int $siteId, ?string $businessType = null): string
    {
        $combined = self::getCombinedStats($siteId, $businessType);
        $primary = $combined['primary'];
        
        $insights = [];
        
        // Overall acceptance rate
        if ($primary['total'] > 0) {
            $insights[] = "Overall acceptance rate: {$primary['acceptance_rate']}%";
        }
        
        // Preferred actions
        if (!empty($primary['preferred_actions'])) {
            $preferred = implode(', ', $primary['preferred_actions']);
            $insights[] = "Users prefer: {$preferred}";
        }
        
        // Avoided actions
        if (!empty($primary['avoided_actions'])) {
            $avoided = implode(', ', $primary['avoided_actions']);
            $insights[] = "Users often reject: {$avoided} - be more careful with these";
        }
        
        // Top performing content globally
        $topContent = self::getTopPerformingContent(3);
        if (!empty($topContent)) {
            $top = implode(', ', $topContent);
            $insights[] = "Most applied content types globally: {$top}";
        }
        
        // Data source
        $insights[] = "Learning source: {$combined['source']} (confidence: {$combined['confidence']})";
        
        if (empty($insights)) {
            return "No feedback data yet - this is a new site.";
        }
        
        return "LEARNING FROM USER FEEDBACK:\n- " . implode("\n- ", $insights);
    }
}
