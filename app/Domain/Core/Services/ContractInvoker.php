<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\Exceptions\NonRetryableExceptionInterface;
use App\Domain\Core\Exceptions\RetryableExceptionInterface;
use App\Domain\Core\Exceptions\ServiceUnavailableException;
use Illuminate\Support\Facades\Log;

class ContractInvoker
{
    private array $circuitState = [];

    public function __construct(
        private IntegrationRegistry $registry,
        private readonly EventDispatcher $eventDispatcher,
        private ?MetricsService $metrics = null,
        private ?DeadLetterService $deadLetter = null,
    ) {}

    public function call(string $contractClass, string $method, array $args = [], ?callable $fallback = null): mixed
    {
        $circuitKey = $contractClass . '@' . $method;
        $startTime = microtime(true);

        if ($this->isCircuitOpen($circuitKey)) {
            if ($fallback) {
                return $fallback();
            }
            throw new ServiceUnavailableException("Circuit breaker open for {$circuitKey}");
        }

        try {
            $provider = $this->registry->resolve($contractClass);

            if (!method_exists($provider, $method)) {
                throw new \BadMethodCallException("Method {$method} not found on {$contractClass}");
            }

            $result = $this->attemptWithRetry($provider, $method, $args, $circuitKey);

            $this->recordSuccess($circuitKey);
            $this->recordMetric($contractClass, $method, true, $startTime);
            $this->eventDispatcher->dispatch('platform.contract.resolved.v1', [
                'contract_class' => $contractClass,
                'method' => $method,
                'provider_id' => get_class($provider),
            ]);
            return $result;

        } catch (RetryableExceptionInterface $e) {
            $this->eventDispatcher->dispatch('platform.contract.failed.v1', [
                'contract_class' => $contractClass,
                'method' => $method,
                'error_message' => $e->getMessage(),
            ]);
            $this->recordFailure($circuitKey);
            $this->recordMetric($contractClass, $method, false, $startTime);
            $this->captureForDlq($contractClass, $method, $args, $e);

            Log::warning('ContractInvoker: call failed', [
                'contract' => $contractClass,
                'method' => $method,
                'error' => $e->getMessage(),
                'circuit' => $circuitKey,
            ]);

            if ($fallback) {
                return $fallback();
            }
            throw $e;
        }
    }

    private function attemptWithRetry(ProviderContract $provider, string $method, array $args, string $circuitKey): mixed
    {
        $maxRetries = 3;
        $attempt = 0;

        while (true) {
            try {
                return $provider->{$method}(...$args);
            } catch (NonRetryableExceptionInterface $e) {
                throw $e;
            } catch (RetryableExceptionInterface $e) {
                $attempt++;
                if ($attempt > $maxRetries) throw $e;
                Log::info("ContractInvoker: retrying {$circuitKey} (attempt {$attempt})");
                usleep($e->retryDelayMs($attempt) * 1000);
            }
        }
    }

    private function isCircuitOpen(string $key): bool
    {
        $state = $this->circuitState[$key] ?? null;
        if (!$state) return false;
        if ($state['state'] === 'closed') return false;
        if ($state['state'] === 'half-open') return false;

        if (($state['opened_at'] ?? 0) + 30 < time()) {
            $this->circuitState[$key]['state'] = 'half-open';
            return false;
        }

        return true;
    }

    private function recordSuccess(string $key): void
    {
        $this->circuitState[$key] = [
            'state' => 'closed',
            'failures' => 0,
        ];
    }

    private function recordFailure(string $key): void
    {
        $state = $this->circuitState[$key] ?? ['state' => 'closed', 'failures' => 0];
        $state['failures']++;

        if ($state['failures'] >= 5) {
            $state['state'] = 'open';
            $state['opened_at'] = time();
            Log::warning("Circuit breaker opened for {$key}");
            $this->eventDispatcher->dispatch('platform.failure.circuit_broken.v1', [
                'contract' => $key,
                'method' => '',
                'failure_count' => $state['failures'],
            ]);
        }

        $this->circuitState[$key] = $state;
    }

    private function recordMetric(string $contractClass, string $method, bool $success, float $startTime): void
    {
        if (!$this->metrics) {
            return;
        }

        $durationMs = (microtime(true) - $startTime) * 1000;
        $this->metrics->recordContractCall($contractClass, $method, $success, $durationMs);
    }

    private function captureForDlq(string $contractClass, string $method, array $args, \Throwable $e): void
    {
        if (!$this->deadLetter) {
            return;
        }

        $this->deadLetter->capture(
            eventName: "contract.{$contractClass}.{$method}",
            payload: ['contract' => $contractClass, 'method' => $method, 'args' => $args],
            errorMessage: $e->getMessage(),
            errorClass: get_class($e),
        );
    }
}
