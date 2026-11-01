-- Database migration for category_id
ALTER TABLE products ADD COLUMN category_id INT;

-- Assuming categories table has category_id and category_name
-- This query sets category_id based on category name match
UPDATE products p
JOIN categories c ON p.category = c.category_name
SET p.category_id = c.category_id;

-- After verifying the data, you can run:
-- ALTER TABLE products DROP COLUMN category;
