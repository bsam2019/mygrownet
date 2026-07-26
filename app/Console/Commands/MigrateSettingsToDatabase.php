<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSettingsToDatabase extends Command
{
    protected $signature = 'platform:migrate-settings
        {--dry-run : Show what would be migrated without writing}';

    protected $description = 'Migrate config values from config/platform.php to the app_settings table';

    protected array $configKeys = [
        'platform.identity.login_url' => ['type' => 'string', 'is_encrypted' => false],
        'platform.identity.register_url' => ['type' => 'string', 'is_encrypted' => false],
        'platform.identity.logout_url' => ['type' => 'string', 'is_encrypted' => false],
        'platform.identity.password_reset_url' => ['type' => 'string', 'is_encrypted' => false],
        'platform.identity.email_verify_url' => ['type' => 'string', 'is_encrypted' => false],
        'platform.identity.signing_key' => ['type' => 'string', 'is_encrypted' => true],
        'platform.identity.return_url_ttl' => ['type' => 'integer', 'is_encrypted' => false],
        'platform.identity.session_domain' => ['type' => 'string', 'is_encrypted' => false],
        'platform.features.unified_auth' => ['type' => 'boolean', 'is_encrypted' => false],
        'platform.queue.default_queue' => ['type' => 'string', 'is_encrypted' => false],
        'platform.queue.listener_timeout' => ['type' => 'integer', 'is_encrypted' => false],
        'platform.queue.retry_attempts' => ['type' => 'integer', 'is_encrypted' => false],
        'platform.queue.dlq_retention_days' => ['type' => 'integer', 'is_encrypted' => false],
    ];

    public function handle(): int
    {
        if (!Schema::hasTable('app_settings')) {
            $this->error('The app_settings table does not exist. Run the migration first.');
            return 1;
        }

        $dryRun = $this->option('dry-run');
        $module = 'platform-core';
        $rows = [];

        foreach ($this->configKeys as $configKey => $meta) {
            $value = config($configKey);

            if (is_null($value)) {
                $this->warn("Skipping {$configKey}: config value is null");
                continue;
            }

            $rows[] = [
                'key' => $configKey,
                'value' => $this->castValue($value, $meta['type']),
                'organization_id' => null,
                'module' => $module,
                'type' => $meta['type'],
                'is_encrypted' => $meta['is_encrypted'],
            ];
        }

        if (empty($rows)) {
            $this->warn('No config values to migrate.');
            return 0;
        }

        $this->info(sprintf('Found %d config values to migrate to app_settings (module: %s)', count($rows), $module));
        $this->newLine();

        $headers = ['Config Key', 'Type', 'Value', 'Encrypted'];
        $tableData = array_map(fn($row) => [
            $row['key'],
            $row['type'],
            $row['type'] === 'boolean' ? ($row['value'] ? 'true' : 'false') : $row['value'],
            $row['is_encrypted'] ? 'Yes' : 'No',
        ], $rows);
        $this->table($headers, $tableData);

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN — no records were written.');
            $this->info('Run without --dry-run to perform the migration.');
            return 0;
        }

        $this->newLine();
        $this->info('Writing to database...');

        try {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    DB::table('app_settings')->updateOrInsert(
                        ['key' => $row['key'], 'module' => $row['module'], 'organization_id' => null],
                        $row
                    );
                }
            });
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }

        $this->info(sprintf('Successfully migrated %d config value(s) to app_settings.', count($rows)));

        return 0;
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) (int) $value,
            default => (string) $value,
        };
    }
}
