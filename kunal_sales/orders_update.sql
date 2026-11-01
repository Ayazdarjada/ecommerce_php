-- Database migration for orders table
ALTER TABLE orders 
ADD COLUMN status ENUM('Pending','Confirmed','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
ADD COLUMN phone VARCHAR(20),
ADD COLUMN address TEXT,
ADD COLUMN city VARCHAR(100),
ADD COLUMN pincode VARCHAR(10);

-- Update orders table payment method
ALTER TABLE orders 
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'COD';
