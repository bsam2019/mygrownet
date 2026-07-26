<?php

namespace App\Domain\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IdempotencyService
{
    private const DEFAULT_LOCK_DURATION = 60;
    private const DEFAULT_KEY_TTL = 86400;

    public function generateKey(string $operation, array $context = []): string
    {
        $parts = [$operation];
        foreach ($context as $key => $value) {
            $parts[] = "{$key}:{$value}";
        }
        return hash('sha256', implode('|', $parts));
    }

    public function execute(string $idempotencyKey, callable $operation, ?int $lockDuration = null, ?int $keyTtl = null): mixed
    {
        $lockDuration ??= self::DEFAULT_LOCK_DURATION;
        $keyTtl ??= self::DEFAULT_KEY_TTL;
        $cacheKey = "idempotency:{$idempotencyKey}";
        $lockKey = "idempotency_lock:{$idempotencyKey}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::debug('Idempotency: returning cached result', ['key' => $idempotencyKey]);
            return $cached;
        }

        if (!Cache::add($lockKey, true, $lockDuration)) {
            Log::info('Idempotency: operation in progress', ['key' => $idempotencyKey]);
            throw new \RuntimeException('Operation is already in progress');
        }

        try {
            $result = $operation();

            Cache::put($cacheKey, $result, $keyTtl);
            Cache::forget($lockKey);

            Log::debug('Idempotency: operation completed', ['key' => $idempotencyKey]);
            return $result;
        } catch (\Throwable $e) {
            Cache::forget($lockKey);
            Log::warning('Idempotency: operation failed', [
                'key' => $idempotencyKey,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function isInProgress(string $idempotencyKey): bool
    {
        $lockKey = "idempotency_lock:{$idempotencyKey}";
        return Cache::has($lockKey);
    }

    public function wasCompleted(string $idempotencyKey): bool
    {
        $cacheKey = "idempotency:{$idempotencyKey}";
        return Cache::has($cacheKey);
    }

    public function clear(string $idempotencyKey): void
    {
        $cacheKey = "idempotency:{$idempotencyKey}";
        $lockKey = "idempotency_lock:{$idempotencyKey}";
        Cache::forget($cacheKey);
        Cache::forget($lockKey);
        Log::debug('Idempotency: cleared', ['key' => $idempotencyKey]);
    }
}
