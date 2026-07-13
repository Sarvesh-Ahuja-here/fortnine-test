<?php

declare(strict_types=1);

require __DIR__ . '/Database.php';
require __DIR__ . '/Cart.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = Database::get();

[$cart, $cartToken] = Cart::fromToken($db, $_COOKIE['cart_token'] ?? null);

if (($_COOKIE['cart_token'] ?? null) !== $cartToken) {
    setcookie('cart_token', $cartToken, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function money(int $cents): string
{
    return '$' . number_format($cents / 100, 2);
}
