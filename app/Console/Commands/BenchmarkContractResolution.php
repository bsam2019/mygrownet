<?php

namespace App\Console\Commands;

use App\Domain\Core\Contracts\NotificationProvider;
use App\Domain\Core\Services\IntegrationRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BenchmarkContractResolution extends Command
{
    protected $signature = 'platform:benchmark-contracts {--iterations=100 : Number of iterations per test} {--port=0 : Port for PHP built-in server (0 = random)}';

    protected $description = 'Benchmark local vs remote contract resolution';

    private ?int $serverPid = null;

    public function handle(IntegrationRegistry $registry): int
    {
        $iterations = (int) $this->option('iterations');

        if ($iterations < 1) {
            $this->error('Iterations must be at least 1.');
            return Command::FAILURE;
        }

        $this->info("Running benchmark with {$iterations} iterations per scenario...");
        $this->newLine();

        $registry->clearCache();

        $results = [];

        // Scenario A: Direct container resolution (baseline)
        try {
            $this->line('Scenario A: Direct app() resolution...');
            $resultA = $this->benchmark(fn () => app(NotificationProvider::class), $iterations);
            $results[] = ['Direct app()', $resultA['total'], $resultA['avg'], $resultA['fastest'], $resultA['slowest']];
            $this->info('  Done.');
        } catch (\Throwable $e) {
            $this->error('  Scenario A failed: ' . $e->getMessage());
            $results[] = ['Direct app()', 'FAILED', "\u{2014}", "\u{2014}", "\u{2014}"];
        }

        $this->newLine();

        // Scenario B: IntegrationRegistry::resolve() (local)
        try {
            $this->line('Scenario B: IntegrationRegistry::resolve()...');
            $resultB = $this->benchmark(fn () => $registry->resolve(NotificationProvider::class), $iterations);
            $results[] = ['Registry::resolve()', $resultB['total'], $resultB['avg'], $resultB['fastest'], $resultB['slowest']];
            $this->info('  Done.');
        } catch (\Throwable $e) {
            $this->error('  Scenario B failed: ' . $e->getMessage());
            $results[] = ['Registry::resolve()', 'FAILED', "\u{2014}", "\u{2014}", "\u{2014}"];
        }

        $this->newLine();

        // Scenario C: Remote HTTP resolution via PHP built-in server
        try {
            $this->line('Scenario C: Remote HTTP (real PHP server)...');
            $port = $this->startPhpServer();
            $resultC = $this->benchmark(fn () => $this->remoteContractCall($port), $iterations);
            $results[] = ['Remote HTTP', $resultC['total'], $resultC['avg'], $resultC['fastest'], $resultC['slowest']];
            $this->info('  Done.');
        } catch (\Throwable $e) {
            $this->error('  Scenario C failed: ' . $e->getMessage());
            $results[] = ['Remote HTTP', 'FAILED', "\u{2014}", "\u{2014}", "\u{2014}"];
        } finally {
            $this->stopPhpServer();
        }

        $this->newLine();
        $this->info('Benchmark Results:');
        $this->newLine();

        $this->table(
            ['Scenario', 'Total (ms)', 'Avg (ms)', 'Fastest (ms)', 'Slowest (ms)'],
            $results
        );

        $this->newLine();

        if (isset($resultA, $resultB, $resultC)) {
            $ratioLocal = round($resultB['avg'] / max($resultA['avg'], 0.001), 2);
            $ratioRemote = round($resultC['avg'] / max($resultB['avg'], 0.001), 2);
            $this->line("Registry is {$ratioLocal}x slower than direct resolution.");
            $this->line("Remote HTTP is {$ratioRemote}x slower than registry resolution.");
        }

        return Command::SUCCESS;
    }

    private function benchmark(callable $fn, int $iterations): array
    {
        $times = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = hrtime(true);

            $fn();

            $elapsed = (hrtime(true) - $start) / 1_000_000;
            $times[] = $elapsed;
        }

        $total = array_sum($times);
        $avg = $total / $iterations;
        $fastest = min($times);
        $slowest = max($times);

        return [
            'total'   => number_format($total, 4),
            'avg'     => number_format($avg, 4),
            'fastest' => number_format($fastest, 4),
            'slowest' => number_format($slowest, 4),
        ];
    }

    private function remoteContractCall(int $port): array
    {
        $response = Http::timeout(5)->post("http://127.0.0.1:{$port}/api/contracts/inventory/stock-level", [
            'item_id' => '42',
            'warehouse_id' => 'main',
        ]);

        return $response->json() ?? [];
    }

    private function startPhpServer(): int
    {
        $port = (int) $this->option('port');

        if ($port <= 0) {
            $port = random_int(18000, 18999);
        }

        $routerPath = base_path('benchmarks/router.php');

        if (!file_exists($routerPath)) {
            throw new \RuntimeException('Router script not found at: ' . $routerPath);
        }

        $docRoot = base_path('public');
        $cmd = sprintf(
            'php -S 127.0.0.1:%d -t %s %s',
            $port,
            escapeshellarg($docRoot),
            escapeshellarg($routerPath)
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', base_path('storage/logs/benchmark-server.log'), 'a'],
            2 => ['file', base_path('storage/logs/benchmark-server.log'), 'a'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start PHP built-in server');
        }

        $status = proc_get_status($process);
        $this->serverPid = $status['running'] ? $status['pid'] : null;

        if (!$this->serverPid) {
            throw new \RuntimeException('PHP server process exited immediately');
        }

        proc_close($process);

        // Wait for the server to accept connections (max 3 seconds)
        $this->waitForServer($port, 3);

        $this->line("  PHP server started on 127.0.0.1:{$port} (PID: {$this->serverPid})");

        return $port;
    }

    private function stopPhpServer(): void
    {
        if ($this->serverPid) {
            $this->line('  Stopping PHP server...');

            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /PID {$this->serverPid} 2>NUL");
            } else {
                exec("kill {$this->serverPid} 2>/dev/null");
            }

            $this->serverPid = null;
        }
    }

    private function waitForServer(int $port, int $maxSeconds): void
    {
        $start = time();

        while (time() - $start < $maxSeconds) {
            $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);

            if ($fp) {
                fclose($fp);
                return;
            }

            usleep(100_000);
        }

        throw new \RuntimeException("PHP server on port {$port} did not become ready within {$maxSeconds}s");
    }
}
