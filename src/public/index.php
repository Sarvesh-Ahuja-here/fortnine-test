<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$products = $db->query('SELECT id, sku, name, price_cents FROM products ORDER BY id')->fetchAll();
$summary = $cart->summary();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body data-csrf="<?= e($_SESSION['csrf_token']) ?>">
    <header class="site-header">
        <h1>Moto Shop</h1>
    </header>

    <main class="layout">
        <section class="products">
            <h2>Products</h2>
            <ul class="product-list">
                <?php foreach ($products as $product): ?>
                    <li class="product-card">
                        <span class="product-sku"><?= e($product['sku']) ?></span>
                        <span class="product-name"><?= e($product['name']) ?></span>
                        <span class="product-price"><?= money((int) $product['price_cents']) ?></span>
                        <button class="btn" data-add="<?= (int) $product['id'] ?>">Add to cart</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="cart">
            <h2>Your Cart</h2>
            <div id="cart-root"
                 data-initial="<?= e(json_encode($summary, JSON_THROW_ON_ERROR)) ?>">
            </div>
        </section>
    </main>

    <script src="assets/cart.js"></script>
</body>
</html>
