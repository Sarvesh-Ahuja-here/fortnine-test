<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

/** @var Cart $cart */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(200, $cart->summary());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Method not allowed']);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, ['error' => 'Invalid JSON body']);
}

if (!hash_equals($_SESSION['csrf_token'], (string) ($input['csrf_token'] ?? ''))) {
    respond(403, ['error' => 'Invalid CSRF token']);
}

$action = (string) ($input['action'] ?? '');
$productId = (int) ($input['product_id'] ?? 0);
$qty = (int) ($input['qty'] ?? 1);

if ($productId < 1) {
    respond(400, ['error' => 'Missing product_id']);
}

switch ($action) {
    case 'add':
        $cart->addItem($productId, $qty);
        break;
    case 'set_qty':
        $cart->setQty($productId, $qty);
        break;
    default:
        respond(400, ['error' => 'Unknown action']);
}

respond(200, $cart->summary());
