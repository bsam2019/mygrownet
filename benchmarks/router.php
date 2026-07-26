<?php
// Router script for PHP built-in server (used by platform:benchmark-contracts)
// Serves as a mock remote contract endpoint for benchmarking HTTP contract resolution.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

// GET /api/contracts/capability — list supported contracts
if ($uri === '/api/contracts/capability' && $method === 'GET') {
    echo json_encode([
        'contracts' => ['inventory', 'notifications'],
        'version' => '1.0',
    ]);
    return true;
}

// POST /api/contracts/inventory/stock-level — getStockLevel
if ($uri === '/api/contracts/inventory/stock-level' && $method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    echo json_encode([
        'status' => 'ok',
        'item_id' => $body['item_id'] ?? null,
        'stock_level' => 42,
        'warehouse' => $body['warehouse_id'] ?? 'default',
    ]);
    return true;
}

// POST /api/contracts/inventory/adjust — adjustStock
if ($uri === '/api/contracts/inventory/adjust' && $method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    echo json_encode([
        'status' => 'ok',
        'item_id' => $body['item_id'] ?? null,
        'new_quantity' => ($body['quantity'] ?? 0) + 100,
        'reason' => $body['reason'] ?? '',
    ]);
    return true;
}

// POST /api/contracts/inventory/items — getItems
if ($uri === '/api/contracts/inventory/items' && $method === 'POST') {
    echo json_encode([
        'status' => 'ok',
        'items' => [
            ['id' => 1, 'name' => 'Widget A', 'quantity' => 50],
            ['id' => 2, 'name' => 'Widget B', 'quantity' => 30],
        ],
    ]);
    return true;
}

// POST /api/contracts/notifications/send — send notification
if ($uri === '/api/contracts/notifications/send' && $method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    echo json_encode([
        'status' => 'ok',
        'notification_id' => 'notif_' . bin2hex(random_bytes(8)),
        'user_id' => $body['user_id'] ?? null,
    ]);
    return true;
}

// GET /health — health check
if ($uri === '/health' && $method === 'GET') {
    echo json_encode(['status' => 'healthy', 'service' => 'benchmark-contracts']);
    return true;
}

// 404 fallback
http_response_code(404);
echo json_encode(['error' => 'Not found', 'uri' => $uri]);
return true;
