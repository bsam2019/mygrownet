<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Idempotency Service
 * 
 * Prevents duplicate operations when network issues cause multiple submissions.
 * Uses a combination of cache-based locking and request tracking.
 */
class IdempotencyService
{
    /**
     * Default lock duration in seconds
     */
    private const DEFAULT_LOCK_DURATION = 60;
    
    /**
     * Default idempotency key TTL in seconds (24 hours)
     */
    private const DEFAULT_KEY_TTL = 86400;

    /**
     * Execute an operation with idempotency protection
     * 
     * @param string $idempotencyKey Unique key for this operation
     * @param callable $operation The operation to execute
     * @param int $lockDuration How long to lock (seconds)
     * @param int $keyTtl How long to remember this key (seconds)
     * @return mixed The result of the operation or cached result
     * @throws \Exception If operation fails
     */
    public function execute(
        string $idempotencyKey,
        callable $operation,
        int $lockDuration = self::DEFAULT_LOCK_DURATION,
        int $keyTtl = self::DEFAULT_KEY_TTL
    ) {
        $cacheKey = "idempotency:{$idempotencyKey}";
        $lockKey = "idempotency_lock:{$idempotencyKey}";
        
        // Check if this operation was already completed
        if (Cache::has($cacheKey)) {
            Log::info('Idempotency: Returning cached result', [
                'key' => $idempotencyKey,
            ]);
            return Cache::get($cacheKey);
        }
        
        // Try to acquire lock
        $lock = Cache::lock($lockKey, $lockDuration);
        
        if (!$lock->get()) {
            // Another request is processing this operation
            Log::warning('Idempotency: Operation already in progress', [
                'key' => $idempotencyKey,
            ]);
            
            // Wait a bit and check if result is available
            sleep(1);
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            throw new \Exception('Operation already in progress. Please wait and try again.');
        }
        
        try {
            // Double-check after acquiring lock
            if (Cache::has($cacheKey)) {
                $lock->release();
                return Cache::get($cacheKey);
            }
            
            // Execute the operation
            Log::info('Idempotency: Executing operation', [
                'key' => $idempotencyKey,
            ]);
            
            $result = $operation();
            
            // Cache the result
            Cache::put($cacheKey, $result, $keyTtl);
            
            Log::info('Idempotency: Operation completed', [
                'key' => $idempotencyKey,
            ]);
            
            return $result;
            
        } finally {
            $lock->release();
        }
    }
    
    /**
     * Generate an idempotency key for a user operation
     * 
     * @param int $userId
     * @param string $operation
     * @param array $params
     * @return string
     */
    public function generateKey(int $userId, string $operation, array $params = []): string
    {
        $paramsHash = md5(json_encode($params));
        return "{$operation}:{$userId}:{$paramsHash}";
    }
    
    /**
     * Check if an operation is already in progress
     * 
     * @param string $idempotencyKey
     * @return bool
     */
    public function isInProgress(string $idempotencyKey): bool
    {
        $lockKey = "idempotency_lock:{$idempotencyKey}";
        return Cache::has($lockKey);
    }
    
    /**
     * Check if an operation was already completed
     * 
     * @param string $idempotencyKey
     * @return bool
     */
    public function wasCompleted(string $idempotencyKey): bool
    {
        $cacheKey = "idempotency:{$idempotencyKey}";
        return Cache::has($cacheKey);
    }
    
    /**
     * Clear an idempotency key (use with caution)
     * 
     * @param string $idempotencyKey
     * @return void
     */
    public function clear(string $idempotencyKey): void
    {
        $cacheKey = "idempotency:{$idempotencyKey}";
        $lockKey = "idempotency_lock:{$idempotencyKey}";
        
        Cache::forget($cacheKey);
        Cache::forget($lockKey);
        
        Log::info('Idempotency: Key cleared', [
            'key' => $idempotencyKey,
        ]);
    }
}
