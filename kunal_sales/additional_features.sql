-- 1. Create service_requests table
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

-- 2. Create wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    PRIMARY KEY (user_id, product_id)
);

-- 3. Sample Products
INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'Luminous Inverter 1100VA', 7500, 'Luminous', 24, 10, 'Reliable power backup solution.'
FROM categories c WHERE c.category_name = 'Inverter' LIMIT 1;

INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'Microtek Inverter 900VA', 6800, 'Microtek', 24, 15, 'Compact and efficient inverter.'
FROM categories c WHERE c.category_name = 'Inverter' LIMIT 1;

INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'Exide Battery 150AH', 12000, 'Exide', 36, 20, 'Long-lasting tubular battery.'
FROM categories c WHERE c.category_name = 'Battery' LIMIT 1;

INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'Amaron Battery 150AH', 12500, 'Amaron', 48, 12, 'High performance maintenance-free battery.'
FROM categories c WHERE c.category_name = 'Battery' LIMIT 1;

INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'Kent RO System', 15000, 'Kent', 12, 5, 'Advanced 7-stage RO water purifier.'
FROM categories c WHERE c.category_name = 'RO System' LIMIT 1;

INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'Aquaguard RO System', 14000, 'Aquaguard', 12, 8, 'Premium water purification system.'
FROM categories c WHERE c.category_name = 'RO System' LIMIT 1;

INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'V-Guard Stabilizer', 2500, 'V-Guard', 24, 25, 'Voltage stabilizer for appliances.'
FROM categories c WHERE c.category_name = 'Stabilizer' LIMIT 1;

INSERT INTO products (category_id, product_name, price, brand, warranty_months, stock, description)
SELECT c.category_id, 'Microtek Stabilizer', 2800, 'Microtek', 24, 18, 'Heavy duty digital voltage stabilizer.'
FROM categories c WHERE c.category_name = 'Stabilizer' LIMIT 1;
