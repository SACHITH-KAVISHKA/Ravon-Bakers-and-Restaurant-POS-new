-- KOT/BOT Auto-Print Setup Script
-- Run this script to configure your items for KOT/BOT printing

-- ========================================
-- STEP 1: Ensure all items have item_type
-- ========================================

-- Check current item types
SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN item_type IS NULL THEN 1 ELSE 0 END) as items_without_type,
    SUM(CASE WHEN item_type = 'Kitchen' THEN 1 ELSE 0 END) as kitchen_items,
    SUM(CASE WHEN item_type = 'Bar' THEN 1 ELSE 0 END) as bar_items,
    SUM(CASE WHEN item_type = 'Both' THEN 1 ELSE 0 END) as both_items
FROM items;

-- Set default type for items without type (set to Kitchen by default)
UPDATE items SET item_type = 'Kitchen' WHERE item_type IS NULL;

-- ========================================
-- STEP 2: Configure Kitchen Items
-- ========================================
-- Set items that should print to Kitchen (KOT)

-- Example Kitchen items - CUSTOMIZE THIS FOR YOUR BUSINESS
UPDATE items SET item_type = 'Kitchen' 
WHERE item_name IN (
    'Pizza',
    'Burger',
    'Pasta',
    'Sandwich',
    'Salad',
    'Fries',
    'Chicken Wings',
    'Soup',
    'Breakfast Items',
    'Main Course'
);

-- Or set by category
UPDATE items SET item_type = 'Kitchen' 
WHERE category IN ('Food', 'Main Dish', 'Side Dish', 'Breakfast', 'Lunch', 'Dinner');

-- ========================================
-- STEP 3: Configure Bar Items
-- ========================================
-- Set items that should print to Bar (BOT)

-- Example Bar items - CUSTOMIZE THIS FOR YOUR BUSINESS
UPDATE items SET item_type = 'Bar' 
WHERE item_name IN (
    'Beer',
    'Wine',
    'Whiskey',
    'Vodka',
    'Rum',
    'Cocktail',
    'Mocktail',
    'Soft Drink',
    'Juice',
    'Water',
    'Soda'
);

-- Or set by category
UPDATE items SET item_type = 'Bar' 
WHERE category IN ('Beverages', 'Alcohol', 'Drinks', 'Soft Drinks');

-- ========================================
-- STEP 4: Configure Items for BOTH
-- ========================================
-- Items that should print to BOTH Kitchen and Bar

-- Example: Coffee, Tea, Milkshakes often need both
UPDATE items SET item_type = 'Both' 
WHERE item_name IN (
    'Coffee',
    'Tea',
    'Cappuccino',
    'Latte',
    'Espresso',
    'Milkshake',
    'Smoothie',
    'Hot Chocolate'
);

-- ========================================
-- STEP 5: Verify Configuration
-- ========================================

-- View all items with their types
SELECT 
    id,
    item_name,
    category,
    item_type,
    is_active
FROM items
ORDER BY item_type, item_name;

-- Summary report
SELECT 
    item_type,
    COUNT(*) as item_count,
    GROUP_CONCAT(item_name SEPARATOR ', ') as items
FROM items
GROUP BY item_type;

-- ========================================
-- STEP 6: Test Data (Optional)
-- ========================================
-- If you want to create some test items

-- Kitchen test items
INSERT INTO items (item_name, item_code, category, item_type, is_active) VALUES
('Test Pizza', 'TPIZZA', 'Food', 'Kitchen', 1),
('Test Burger', 'TBURGER', 'Food', 'Kitchen', 1),
('Test Pasta', 'TPASTA', 'Food', 'Kitchen', 1);

-- Bar test items
INSERT INTO items (item_name, item_code, category, item_type, is_active) VALUES
('Test Beer', 'TBEER', 'Beverages', 'Bar', 1),
('Test Cocktail', 'TCOCKTAIL', 'Beverages', 'Bar', 1),
('Test Juice', 'TJUICE', 'Beverages', 'Bar', 1);

-- Both test items
INSERT INTO items (item_name, item_code, category, item_type, is_active) VALUES
('Test Coffee', 'TCOFFEE', 'Beverages', 'Both', 1),
('Test Tea', 'TTEA', 'Beverages', 'Both', 1);

-- ========================================
-- VERIFICATION QUERIES
-- ========================================

-- Check if KOT/BOT tables exist
SELECT TABLE_NAME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('kots', 'kot_items');

-- Check recent KOTs/BOTs
SELECT 
    kot_no,
    type,
    status,
    created_at
FROM kots
ORDER BY created_at DESC
LIMIT 10;

-- Check KOT items
SELECT 
    k.kot_no,
    k.type,
    ki.item_name,
    ki.quantity,
    ki.total_price
FROM kots k
JOIN kot_items ki ON k.id = ki.kot_id
ORDER BY k.created_at DESC
LIMIT 20;

-- ========================================
-- CLEANUP (If needed)
-- ========================================

-- Delete test KOTs (if you created test data)
-- DELETE FROM kot_items WHERE kot_id IN (SELECT id FROM kots WHERE notes LIKE '%test%');
-- DELETE FROM kots WHERE notes LIKE '%test%';

-- Reset item types to default
-- UPDATE items SET item_type = 'Kitchen';

-- ========================================
-- NOTES
-- ========================================
-- 1. Customize the item names above to match YOUR actual items
-- 2. Run queries one section at a time
-- 3. Always backup your database before running UPDATE queries
-- 4. Test with a few items first before updating all items
-- 5. item_type values are: 'Kitchen', 'Bar', 'Both'
-- ========================================
