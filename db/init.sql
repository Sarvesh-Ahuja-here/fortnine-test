CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    price_cents INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token CHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    qty INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_cart_product (cart_id, product_id),
    CONSTRAINT fk_items_cart FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO products (sku, name, price_cents) VALUES
('HEL-001', 'Shoei RF-1400 Helmet', 65999),
('GLV-002', 'Alpinestars SP-8 v3 Gloves', 11995),
('JKT-003', 'Dainese Super Speed 4 Jacket', 74995),
('BTS-004', 'TCX Street 3 WP Boots', 21999),
('LCK-005', 'Kryptonite New York Disc Lock', 13449),
('OIL-006', 'Motul 7100 10W40 Synthetic Oil 4L', 6299);
