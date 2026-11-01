-- Update products table with new columns
ALTER TABLE products 
ADD COLUMN brand VARCHAR(100),
ADD COLUMN warranty_months INT,
ADD COLUMN specs TEXT,
ADD COLUMN stock INT DEFAULT 0;
