-- =====================================================================
-- fix_schema.sql  --  ONE script that brings the database up to date.
-- Safe to run more than once, and safe whether or not you already ran
-- migration_prompt_1.sql / products_update.sql / orders_update.sql /
-- additional_features.sql (those four are now redundant).
--
-- Run order for a fresh DB:   kunal_sales.sql  ->  fix_schema.sql
-- Requires MariaDB (XAMPP default). MySQL 8 does not support
-- "ADD COLUMN IF NOT EXISTS".
-- =====================================================================

-- ---------- products ----------
ALTER TABLE products
  ADD COLUMN IF NOT EXISTS category_id INT NULL,
  ADD COLUMN IF NOT EXISTS brand VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS warranty_months INT NULL,
  ADD COLUMN IF NOT EXISTS specs TEXT NULL,
  ADD COLUMN IF NOT EXISTS stock INT NOT NULL DEFAULT 0;

-- Map the old ENUM `category` to category_id.
-- The old migration missed 'RO' because the category is named 'RO System'.
UPDATE products p
JOIN categories c
  ON c.category_name = CASE p.category WHEN 'RO' THEN 'RO System' ELSE p.category END
SET p.category_id = c.category_id
WHERE p.category_id IS NULL AND p.category IS NOT NULL;

ALTER TABLE products ADD INDEX IF NOT EXISTS idx_products_category (category_id);

-- ---------- orders ----------
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS status ENUM('Pending','Confirmed','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS address TEXT NULL,
  ADD COLUMN IF NOT EXISTS city VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS pincode VARCHAR(10) NULL,
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL DEFAULT 'COD';

-- ---------- tables from additional_features.sql (not used by the PHP yet) ----------
CREATE TABLE IF NOT EXISTS service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    service_type ENUM('Inverter repair', 'Battery replacement', 'RO service', 'Installation') NOT NULL,
    preferred_date DATE NOT NULL,
    message TEXT,
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS wishlist (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    PRIMARY KEY (user_id, product_id)
);

-- ---------- foreign keys ----------
-- Deleting a product used to silently DELETE its rows from past orders
-- (order_items ... ON DELETE CASCADE). Now it is blocked instead.
ALTER TABLE order_items DROP FOREIGN KEY IF EXISTS order_items_ibfk_2;
ALTER TABLE order_items
  ADD CONSTRAINT order_items_ibfk_2 FOREIGN KEY (product_id)
  REFERENCES products (product_id) ON DELETE RESTRICT;

-- Deleting a product used to fail if it was sitting in anyone's cart.
ALTER TABLE cart DROP FOREIGN KEY IF EXISTS cart_ibfk_2;
ALTER TABLE cart
  ADD CONSTRAINT cart_ibfk_2 FOREIGN KEY (product_id)
  REFERENCES products (product_id) ON DELETE CASCADE;

-- One cart row per (user, product): merge any duplicates first, then enforce it.
UPDATE cart c
JOIN (SELECT user_id, product_id, MIN(cart_id) AS keep_id, SUM(quantity) AS qty
      FROM cart GROUP BY user_id, product_id HAVING COUNT(*) > 1) d
  ON c.cart_id = d.keep_id
SET c.quantity = d.qty;

DELETE c1 FROM cart c1
JOIN cart c2 ON c1.user_id = c2.user_id AND c1.product_id = c2.product_id
            AND c1.cart_id > c2.cart_id;

ALTER TABLE cart ADD UNIQUE INDEX IF NOT EXISTS uq_cart_user_product (user_id, product_id);
