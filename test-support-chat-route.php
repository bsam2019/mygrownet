<?php

// Test script to verify support chat route
// Run: php test-support-chat-route.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check if route exists
$routes = app('router')->getRoutes();
$chatRoute = $routes->getByName('employee.portal.support.chat');

if ($chatRoute) {
    echo "✓ Route exists: employee.portal.support.chat\n";
    echo "  URI: " . $chatRoute->uri() . "\n";
    echo "  Methods: " . implode(', ', $chatRoute->methods()) . "\n";
    echo "  Action: " . $chatRoute->getActionName() . "\n";
    echo "  Middleware: " . implode(', ', $chatRoute->middleware()) . "\n";
} else {
    echo "✗ Route NOT found: employee.portal.support.chat\n";
}

// Check employee middleware
echo "\n";
echo "Checking middleware...\n";
$middleware = app('router')->getMiddleware();
if (isset($middleware['employee'])) {
    echo "✓ Employee middleware registered\n";
    echo "  Class: " . $middleware['employee'] . "\n";
} else {
    echo "✗ Employee middleware NOT registered\n";
}

echo "\nDone.\n";
