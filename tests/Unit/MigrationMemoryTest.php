<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MigrationMemoryTest extends TestCase
{
    public function test_basic_migration_memory_usage()
    {
        $memoryBefore = memory_get_usage();
        
        // Try to run just the basic Laravel migrations
        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/0001_01_01_000001_create_cache_table.php',
            '--force' => true
        ]);
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed); // Less than 50MB
        $this->assertTrue(Schema::hasTable('cache'));
    }

    public function test_users_table_migration()
    {
        $memoryBefore = memory_get_usage();
        
        // Try to run just the users table migration
        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/2014_10_12_000000_create_users_table.php',
            '--force' => true
        ]);
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed); // Less than 50MB
        $this->assertTrue(Schema::hasTable('users'));
    }
}