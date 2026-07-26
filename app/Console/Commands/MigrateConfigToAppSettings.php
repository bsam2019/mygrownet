<?php

namespace App\Console\Commands;

use App\Domain\Core\Services\SettingsService;
use Illuminate\Console\Command;

class MigrateConfigToAppSettings extends Command
{
    protected $signature = 'platform:migrate-config {--dry-run : Show what would be migrated without writing}';

    protected $description = 'Migrate config/platform.php values into the app_settings table';

    private const SETTINGS_MAP = [
        'platform.features.unified_auth' => [
            'key' => 'features.unified_auth',
            'module' => 'platform',
            'type' => 'boolean',
            'source' => 'platform.features.unified_auth',
        ],
        'platform.organization_prefix' => [
            'key' => 'workspace.org_prefix',
            'module' => 'platform',
            'type' => 'string',
            'source' => 'platform.organization_prefix',
        ],
        'platform.identity.login_url' => [
            'key' => 'identity.login_url',
            'module' => 'identity',
            'type' => 'string',
            'source' => 'platform.identity.login_url',
        ],
        'platform.identity.register_url' => [
            'key' => 'identity.register_url',
            'module' => 'identity',
            'type' => 'string',
            'source' => 'platform.identity.register_url',
        ],
        'platform.identity.logout_url' => [
            'key' => 'identity.logout_url',
            'module' => 'identity',
            'type' => 'string',
            'source' => 'platform.identity.logout_url',
        ],
        'platform.identity.password_reset_url' => [
            'key' => 'identity.password_reset_url',
            'module' => 'identity',
            'type' => 'string',
            'source' => 'platform.identity.password_reset_url',
        ],
        'platform.identity.email_verify_url' => [
            'key' => 'identity.email_verify_url',
            'module' => 'identity',
            'type' => 'string',
            'source' => 'platform.identity.email_verify_url',
        ],
        'platform.identity.return_url_ttl' => [
            'key' => 'identity.return_url_ttl',
            'module' => 'identity',
            'type' => 'integer',
            'source' => 'platform.identity.return_url_ttl',
        ],
        'platform.identity.session_domain' => [
            'key' => 'identity.session_domain',
            'module' => 'identity',
            'type' => 'string',
            'source' => 'platform.identity.session_domain',
        ],
        'platform.identity.rate_limiting.per_ip' => [
            'key' => 'identity.rate_limit.per_ip',
            'module' => 'identity',
            'type' => 'integer',
            'source' => 'platform.identity.rate_limiting.per_ip',
        ],
        'platform.identity.rate_limiting.per_user' => [
            'key' => 'identity.rate_limit.per_user',
            'module' => 'identity',
            'type' => 'integer',
            'source' => 'platform.identity.rate_limiting.per_user',
        ],
        'platform.queue.default_queue' => [
            'key' => 'queue.default_queue',
            'module' => 'platform',
            'type' => 'string',
            'source' => 'platform.queue.default_queue',
        ],
        'platform.queue.listener_timeout' => [
            'key' => 'queue.listener_timeout',
            'module' => 'platform',
            'type' => 'integer',
            'source' => 'platform.queue.listener_timeout',
        ],
        'platform.queue.retry_attempts' => [
            'key' => 'queue.retry_attempts',
            'module' => 'platform',
            'type' => 'integer',
            'source' => 'platform.queue.retry_attempts',
        ],
        'platform.queue.dlq_retention_days' => [
            'key' => 'queue.dlq_retention_days',
            'module' => 'platform',
            'type' => 'integer',
            'source' => 'platform.queue.dlq_retention_days',
        ],
    ];

    public function handle(SettingsService $settings): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $migrated = 0;
        $skipped = 0;

        foreach (self::SETTINGS_MAP as $configPath => $mapping) {
            $value = data_get(config(), $configPath);

            if (is_null($value) || $value === '' || $value === false) {
                $this->warn("  SKIP {$mapping['key']}: source config '{$configPath}' is null/empty/false");
                $skipped++;
                continue;
            }

            $existing = $settings->get($mapping['key'], '__NOT_FOUND__', null, $mapping['module']);

            if ($existing !== '__NOT_FOUND__') {
                $this->line("  SKIP {$mapping['key']}: already exists in app_settings (value: {$existing})");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  WOULD SET {$mapping['key']} = {$value} [{$mapping['type']}] module:{$mapping['module']}");
            } else {
                $settings->set($mapping['key'], $value, null, $mapping['module'], $mapping['type']);
                $this->line("  SET {$mapping['key']} = {$value} [{$mapping['type']}]");
            }

            $migrated++;
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run complete. Would migrate: {$migrated}, Would skip: {$skipped}");
        } else {
            $this->info("Migration complete. Migrated: {$migrated}, Skipped (already exist): {$skipped}");
        }

        return Command::SUCCESS;
    }
}
