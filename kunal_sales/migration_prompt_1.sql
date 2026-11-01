-- 1. SQL for category_id migration
ALTER TABLE products ADD COLUMN category_id INT;

-- This is a placeholder; you may need to run multiple UPDATE statements 
-- if category names don't map directly to IDs.
UPDATE products p
JOIN categories c ON p.category = c.category_name
SET p.category_id = c.category_id;

-- 2. SQL to update orders table for Prompt 1 (if needed) - none specified here, 
-- but ensuring database is clean.
