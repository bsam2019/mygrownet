<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNetwork extends Model
{
    protected $fillable = [
        'user_id',
        'referrer_id',
        'level',
        'path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get all users in the network up to specified level
     */
    public static function getNetworkMembers(int $userId, int $maxLevel = 5): array
    {
        return self::where('referrer_id', $userId)
            ->where('level', '<=', $maxLevel)
            ->with('user')
            ->get()
            ->groupBy('level')
            ->toArray();
    }

    /**
     * Build materialized path for efficient queries
     */
    public static function buildPath(int $userId, int $referrerId): string
    {
        $referrerPath = self::where('user_id', $referrerId)->value('path');
        
        if ($referrerPath) {
            return $referrerPath . '.' . $userId;
        }
        
        return (string) $userId;
    }

    /**
     * Get all upline referrers up to 5 levels
     */
    public static function getUplineReferrers(int $userId, int $maxLevel = 5): array
    {
        $path = self::where('user_id', $userId)->value('path');
        
        if (!$path) {
            return [];
        }
        
        $pathArray = explode('.', $path);
        $upline = [];
        
        // Remove the user's own ID from the path
        array_pop($pathArray);
        
        // Get up to 5 levels of upline
        $levels = array_slice(array_reverse($pathArray), 0, $maxLevel);
        
        foreach ($levels as $index => $referrerId) {
            $upline[] = [
                'user_id' => (int) $referrerId,
                'level' => $index + 1
            ];
        }
        
        return $upline;
    }
}