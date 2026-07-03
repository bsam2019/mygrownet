<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = $app['db'];
$tables = ['growmart_products', 'growmart_categories', 'growmart_inventory', 'growmart_warehouses', 'growbuilder_sites', 'bizboost_business_profiles', 'matrix_positions'];
foreach ($tables as $t) {
    try {
        $r = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name='$t'");
        echo "$t: " . (count($r) > 0 ? "OK" : "MISSING") . "\n";
    } catch (Exception $e) {
        echo "$t: ERROR - " . $e->getMessage() . "\n";
    }
}
echo "\nTotal migrations in DB: ";
$cnt = $db->select("SELECT count(*) as c FROM migrations");
echo $cnt[0]->c . "\n";

