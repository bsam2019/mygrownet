<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select("SHOW TABLES");
$dbName = env('DB_DATABASE', 'mygrownet');

echo "=== Investor-related Tables ===\n";
foreach ($tables as $table) {
    $tableName = $table->{"Tables_in_$dbName"} ?? array_values((array)$table)[0];
    if (str_contains($tableName, 'investor') || 
        str_contains($tableName, 'share') || 
        str_contains($tableName, 'dividend') || 
        str_contains($tableName, 'forum') || 
        str_contains($tableName, 'liquidity') ||
        str_contains($tableName, 'quarterly') ||
        str_contains($tableName, 'board') ||
        str_contains($tableName, 'meeting')) {
        echo "✓ $tableName\n";
    }
}
