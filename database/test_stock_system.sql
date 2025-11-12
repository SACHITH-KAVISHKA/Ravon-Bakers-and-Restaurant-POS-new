-- ============================================================
-- STOCK SYSTEM TEST SCRIPT
-- Run this to create test data and verify your system
-- ============================================================

-- STEP 1: CREATE TEST PRODUCT
-- ============================================================
INSERT INTO items (item_name, item_code, category, is_active, created_at, updated_at)
VALUES ('Test Product 123', 'TEST-123', 'Test', 1, NOW(), NOW());

-- Get the ID (will be used in subsequent queries)
SET @test_item_id = LAST_INSERT_ID();
SELECT CONCAT('✅ Created test item with ID: ', @test_item_id) AS Status;

-- Create initial inventory record for Main Branch (ID=1)
INSERT INTO inventories (item_id, branch_id, current_stock, low_stock_alert, created_at, updated_at)
VALUES (@test_item_id, 1, 0, 10, NOW(), NOW());

SELECT CONCAT('✅ Created inventory record with 0 stock') AS Status;

-- Check initial stock
SELECT 
    i.item_name,
    i.item_code,
    inv.current_stock AS 'Current Stock',
    'Should be 0' AS 'Expected'
FROM items i
JOIN inventories inv ON i.id = inv.item_id
WHERE i.id = @test_item_id AND inv.branch_id = 1;


-- STEP 2: CREATE PRODUCTION (STOCK IN)
-- ============================================================
INSERT INTO inventory_requests (user_id, department_id, date_time, status, created_at, updated_at)
VALUES (1, 1, '2025-11-10 09:00:00', 'completed', NOW(), NOW());

SET @request_id = LAST_INSERT_ID();
SELECT CONCAT('✅ Created inventory request ID: ', @request_id) AS Status;

-- Add items to production
INSERT INTO inventory_request_items (inventory_request_id, item_id, quantity, created_at, updated_at)
VALUES (@request_id, @test_item_id, 100, NOW(), NOW());

SELECT '✅ Added 100 units in production' AS Status;

-- Update inventory
UPDATE inventories 
SET current_stock = current_stock + 100,
    updated_at = NOW()
WHERE item_id = @test_item_id AND branch_id = 1;

SELECT '✅ Updated inventory +100' AS Status;

-- Check stock after production
SELECT 
    i.item_name,
    i.item_code,
    inv.current_stock AS 'Current Stock',
    'Should be 100' AS 'Expected',
    CASE 
        WHEN inv.current_stock = 100 THEN '✅ PASS'
        ELSE '❌ FAIL'
    END AS 'Test Result'
FROM items i
JOIN inventories inv ON i.id = inv.item_id
WHERE i.id = @test_item_id AND inv.branch_id = 1;


-- STEP 3: CREATE SALE (STOCK OUT)
-- ============================================================
-- Note: Sales table uses created_at for timestamp, not 'date' column
INSERT INTO sales (receipt_no, terminal, user_name, subtotal, discount, tax, total, payment_method, user_id, branch_id, status, created_at, updated_at)
VALUES ('TEST-001', '01', 'Test User', 50.00, 0.00, 0.00, 50.00, 'cash', 1, 1, 1, '2025-11-11 14:00:00', '2025-11-11 14:00:00');

SET @sale_id = LAST_INSERT_ID();
SELECT CONCAT('✅ Created sale ID: ', @sale_id) AS Status;

-- Add sale item
INSERT INTO sale_items (sale_id, item_id, item_name, quantity, unit_price, total_price, created_at, updated_at)
VALUES (@sale_id, @test_item_id, 'Test Product 123', 20, 2.50, 50.00, NOW(), NOW());

SELECT '✅ Added sale item: 20 units' AS Status;

-- Update inventory
UPDATE inventories 
SET current_stock = current_stock - 20,
    updated_at = NOW()
WHERE item_id = @test_item_id AND branch_id = 1;

SELECT '✅ Updated inventory -20' AS Status;

-- Check stock after sale
SELECT 
    i.item_name,
    i.item_code,
    inv.current_stock AS 'Current Stock',
    'Should be 80' AS 'Expected',
    CASE 
        WHEN inv.current_stock = 80 THEN '✅ PASS'
        ELSE '❌ FAIL'
    END AS 'Test Result'
FROM items i
JOIN inventories inv ON i.id = inv.item_id
WHERE i.id = @test_item_id AND inv.branch_id = 1;


-- STEP 4: VERIFY ALL TRANSACTIONS
-- ============================================================
SELECT '=' AS '====================================';
SELECT 'TRANSACTION SUMMARY' AS 'Report';
SELECT '=' AS '====================================';

-- Show production
SELECT 
    'Production' AS 'Transaction Type',
    ir.date_time AS 'Date/Time',
    iri.quantity AS 'Quantity',
    '+100' AS 'Expected'
FROM inventory_requests ir
JOIN inventory_request_items iri ON ir.id = iri.inventory_request_id
WHERE iri.item_id = @test_item_id;

-- Show sale
SELECT 
    'Sale' AS 'Transaction Type',
    s.created_at AS 'Date/Time',
    si.quantity AS 'Quantity',
    '-20' AS 'Expected'
FROM sales s
JOIN sale_items si ON s.id = si.sale_id
WHERE si.item_id = @test_item_id;

-- Show final stock
SELECT 
    'Final Stock' AS 'Type',
    inv.updated_at AS 'Last Updated',
    inv.current_stock AS 'Current Stock',
    '80' AS 'Expected'
FROM inventories inv
WHERE inv.item_id = @test_item_id AND inv.branch_id = 1;


-- STEP 5: TEST HISTORICAL CALCULATION (MANUAL)
-- ============================================================
SELECT '=' AS '====================================';
SELECT 'HISTORICAL STOCK TEST' AS 'Report';
SELECT '=' AS '====================================';

-- Calculate stock before sale (as of 2025-11-10 23:59:59)
SELECT 
    'Before Sale (2025-11-10 23:59:59)' AS 'Scenario',
    SUM(iri.quantity) AS 'Total Productions',
    0 AS 'Total Sales',
    100 AS 'Expected Stock',
    CASE 
        WHEN SUM(iri.quantity) = 100 THEN '✅ PASS'
        ELSE '❌ FAIL'
    END AS 'Test Result'
FROM inventory_requests ir
JOIN inventory_request_items iri ON ir.id = iri.inventory_request_id
WHERE iri.item_id = @test_item_id
  AND ir.status = 'completed'
  AND ir.date_time <= '2025-11-10 23:59:59';

-- Calculate stock after sale (as of 2025-11-11 23:59:59)
SELECT 
    'After Sale (2025-11-11 23:59:59)' AS 'Scenario',
    (SELECT SUM(iri.quantity)
     FROM inventory_requests ir
     JOIN inventory_request_items iri ON ir.id = iri.inventory_request_id
     WHERE iri.item_id = @test_item_id
       AND ir.status = 'completed'
       AND ir.date_time <= '2025-11-11 23:59:59') AS 'Total Productions',
    (SELECT SUM(si.quantity)
     FROM sales s
     JOIN sale_items si ON s.id = si.sale_id
     WHERE si.item_id = @test_item_id
       AND s.status = 1
       AND s.created_at <= '2025-11-11 23:59:59') AS 'Total Sales',
    100 - 20 AS 'Expected Stock (100-20)',
    CASE 
        WHEN (100 - 20) = 80 THEN '✅ PASS'
        ELSE '❌ FAIL'
    END AS 'Test Result';


-- CLEANUP (Optional - uncomment to remove test data)
-- ============================================================
/*
DELETE FROM sale_items WHERE sale_id = @sale_id;
DELETE FROM sales WHERE id = @sale_id;
DELETE FROM inventory_request_items WHERE inventory_request_id = @request_id;
DELETE FROM inventory_requests WHERE id = @request_id;
DELETE FROM inventories WHERE item_id = @test_item_id;
DELETE FROM items WHERE id = @test_item_id;
SELECT '✅ Test data cleaned up' AS Status;
*/


-- FINAL SUMMARY
-- ============================================================
SELECT '=' AS '====================================';
SELECT 'TEST SUMMARY' AS 'Report';
SELECT '=' AS '====================================';

SELECT 
    'Test Product 123' AS 'Item',
    @test_item_id AS 'Item ID',
    (SELECT current_stock FROM inventories WHERE item_id = @test_item_id AND branch_id = 1) AS 'Current Stock',
    80 AS 'Expected Stock',
    CASE 
        WHEN (SELECT current_stock FROM inventories WHERE item_id = @test_item_id AND branch_id = 1) = 80 
        THEN '✅ ALL TESTS PASSED'
        ELSE '❌ TEST FAILED - Check inventory updates'
    END AS 'Overall Result';

SELECT '
NEXT STEPS:
1. Check the Stock Report in the application (Supervisor > Stock Report)
2. Try filtering by date: 2025-11-10 and time: 23:59:59 (should show 100)
3. Try filtering by date: 2025-11-11 and time: 23:59:59 (might show 100 instead of 80!)
4. If step 3 shows 100 instead of 80, sales are NOT included in historical calculations

KNOWN ISSUE:
The historical stock calculation in SupervisorController::calculateStockAtDateTime()
does NOT include sales, which will cause incorrect historical reports.
' AS 'Instructions';
