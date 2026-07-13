<?php

declare(strict_types=1);

final class Cart
{
    public const GST_RATE = 0.05;
    public const QST_RATE = 0.09975;

    private PDO $db;
    private int $id;

    private function __construct(PDO $db, int $id)
    {
        $this->db = $db;
        $this->id = $id;
    }

    /**
     * Load the cart matching $token, or create a new one when the token is
     * empty or unknown. Returns the cart and the token to persist client-side.
     *
     * @return array{0: self, 1: string}
     */
    public static function fromToken(PDO $db, ?string $token): array
    {
        if ($token !== null && preg_match('/^[a-f0-9]{64}$/', $token)) {
            $stmt = $db->prepare('SELECT id FROM carts WHERE token = ?');
            $stmt->execute([$token]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return [new self($db, (int) $id), $token];
            }
        }

        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare('INSERT INTO carts (token) VALUES (?)');
        $stmt->execute([$token]);

        return [new self($db, (int) $db->lastInsertId()), $token];
    }

    public function addItem(int $productId, int $qty): void
    {
        $qty = max(1, min($qty, 99));

        $stmt = $this->db->prepare(
            'INSERT INTO cart_items (cart_id, product_id, qty)
             SELECT ?, id, ? FROM products WHERE id = ?
             ON DUPLICATE KEY UPDATE qty = LEAST(qty + VALUES(qty), 99)'
        );
        $stmt->execute([$this->id, $qty, $productId]);
    }

    public function setQty(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?');
            $stmt->execute([$this->id, $productId]);
            return;
        }

        $stmt = $this->db->prepare('UPDATE cart_items SET qty = ? WHERE cart_id = ? AND product_id = ?');
        $stmt->execute([min($qty, 99), $this->id, $productId]);
    }

    /**
     * @return array{items: list<array<string, mixed>>, subtotal_cents: int,
     *               gst_cents: int, qst_cents: int, total_cents: int}
     */
    public function summary(): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id AS product_id, p.sku, p.name, p.price_cents, i.qty,
                    p.price_cents * i.qty AS row_total_cents
             FROM cart_items i
             JOIN products p ON p.id = i.product_id
             WHERE i.cart_id = ?
             ORDER BY i.id'
        );
        $stmt->execute([$this->id]);
        $items = $stmt->fetchAll();

        $subtotal = 0;
        foreach ($items as &$item) {
            $item['product_id'] = (int) $item['product_id'];
            $item['price_cents'] = (int) $item['price_cents'];
            $item['qty'] = (int) $item['qty'];
            $item['row_total_cents'] = (int) $item['row_total_cents'];
            $subtotal += $item['row_total_cents'];
        }
        unset($item);

        $gst = (int) round($subtotal * self::GST_RATE);
        $qst = (int) round($subtotal * self::QST_RATE);

        return [
            'items' => $items,
            'subtotal_cents' => $subtotal,
            'gst_cents' => $gst,
            'qst_cents' => $qst,
            'total_cents' => $subtotal + $gst + $qst,
        ];
    }
}
