<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "Searching for Esaya in the database...\n\n";

$users = User::where('name', 'like', '%Esaya%')
    ->orWhere('email', 'like', '%esaya%')
    ->get(['id', 'name', 'email', 'phone']);

if ($users->isEmpty()) {
    echo "No users found matching 'Esaya' in local database.\n";
    echo "\nNote: The production database has Esaya Nkhata as User ID 11.\n";
    echo "This local database appears to have test/seed data.\n";
} else {
    echo "Found " . $users->count() . " user(s):\n";
    foreach ($users as $user) {
        echo "  ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
}
