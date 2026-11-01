-- 1. Create service_requests table
CREATE TABLE service_requests (
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
CREATE TABLE wishlist (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    PRIMARY KEY (user_id, product_id)
);

-- 3. Sample Products (assuming categories exist, adjust category_id accordingly)
-- Ensure you have run previous migrations to add the columns to products table first.
INSERT INTO products (category_id, name, price, brand, warranty_months, stock, description) VALUES
(1, 'Luminous Inverter 1100VA', 7500, 'Luminous', 24, 10, 'Reliable power backup solution.'),
(1, 'Microtek Inverter 900VA', 6800, 'Microtek', 24, 15, 'Compact and efficient inverter.'),
(2, 'Exide Battery 150AH', 12000, 'Exide', 36, 20, 'Long-lasting tubular battery.'),
(2, 'Amaron Battery 150AH', 12500, 'Amaron', 48, 12, 'High performance maintenance-free battery.'),
(3, 'Kent RO System', 15000, 'Kent', 12, 5, 'Advanced 7-stage RO water purifier.'),
(3, 'Aquaguard RO System', 14000, 'Aquaguard', 12, 8, 'Premium water purification system.'),
(4, 'V-Guard Stabilizer', 2500, 'V-Guard', 24, 25, 'Voltage stabilizer for appliances.'),
(4, 'Microtek Stabilizer', 2800, 'Microtek', 24, 18, 'Heavy duty digital voltage stabilizer.');
