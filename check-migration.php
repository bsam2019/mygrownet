<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check if migration is in the migrations table
$migration = DB::table('migrations')->where('migration', 'like', '%investor_messages%')->first();
if ($migration) {
    echo "Migration found: {$migration->migration} (batch {$migration->batch})\n";
} else {
    echo "Migration not found in migrations table. Adding it...\n";
    DB::table('migrations')->insert([
        'migration' => '2024_11_27_000001_create_investor_messages_table',
        'batch' => 52,
    ]);
    echo "Migration marked as run.\n";
}
