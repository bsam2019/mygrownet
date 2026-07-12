<?php
require "/var/www/mygrownet.com/vendor/autoload.php";
$app = require "/var/www/mygrownet.com/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Migrations ===\n";
$migrations = DB::select("SELECT migration, batch FROM migrations WHERE migration LIKE '%sa_%' ORDER BY batch, migration");
foreach ($migrations as $m) {
    echo "  {$m->migration} [batch {$m->batch}]\n";
}

echo "\n=== sa_ tables ===\n";
$tables = DB::select("SHOW TABLES LIKE 'sa_%'");
foreach ($tables as $t) {
    $name = array_values((array)$t)[0];
    echo "  $name\n";
}
echo "\n";
