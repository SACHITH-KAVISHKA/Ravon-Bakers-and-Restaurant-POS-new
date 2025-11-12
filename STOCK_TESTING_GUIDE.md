# Stock System Testing Guide

## Current System Analysis

Your project **DOES NOT** have a `stock_transactions` table as mentioned in the test plan. Instead, it tracks inventory through multiple specialized tables:

1. **`inventories`** - Stores current stock levels per item per branch
2. **`purchases`** - Records stock purchases (stock in)
3. **`sales` & `sale_items`** - Records sales transactions (stock out)
4. **`stock_transfers`** - Records transfers between branches
5. **`wastages` & `wastage_items`** - Records wastage
6. **`inventory_requests` & `inventory_request_items`** - Production/stock additions

## System Architecture

### Current Stock Tracking:
- Each item has an `inventories` record per branch with `current_stock` field
- Stock is **directly modified** when transactions occur (not calculated from transactions)
- Historical stock levels are **reconstructed** by calculating from all transaction types

### Stock Changes Flow:
1. **Purchase** → Not currently updating inventory automatically
2. **Production (Inventory Request)** → Adds to Main Branch inventory
3. **Sale** → Reduces branch inventory
4. **Stock Transfer** → Moves stock from one branch to another
5. **Wastage** → Reduces inventory

## Issues Identified

### ❌ Missing Features:
1. **No `stock_transactions` table** - Cannot track transaction history in a unified way
2. **No Stock Activity Report** - Cannot show transaction list by date range
3. **No Stock on Hand Report** - The existing report doesn't support "as of date" snapshots
4. **Purchases don't update inventory** - Purchase records exist but don't affect stock levels

## Recommended Testing Approach (Current System)

Since you don't have the `stock_transactions` table from the test plan, here's how to test your **existing system**:

### Step 1: Create Test Product
```sql
-- Insert test item
INSERT INTO items (item_name, item_code, category, is_active, created_at, updated_at)
VALUES ('Test Product 123', 'TEST-123', 'Test Category', 1, NOW(), NOW());

-- Get the ID (assume it's 999 for this example)
SET @item_id = LAST_INSERT_ID();

-- Create initial inventory record for Main Branch (ID = 1)
INSERT INTO inventories (item_id, branch_id, current_stock, low_stock_alert, created_at, updated_at)
VALUES (@item_id, 1, 0, 10, NOW(), NOW());
```

### Step 2: Test Production (Stock In via Inventory Request)
```sql
-- Create inventory request (production)
INSERT INTO inventory_requests (user_id, department_id, date_time, status, created_at, updated_at)
VALUES (1, 1, '2025-11-10 09:00:00', 'completed', NOW(), NOW());

SET @request_id = LAST_INSERT_ID();

-- Add item to request
INSERT INTO inventory_request_items (inventory_request_id, item_id, quantity, created_at, updated_at)
VALUES (@request_id, @item_id, 100, NOW(), NOW());

-- Update inventory (this might be done by your application automatically)
UPDATE inventories SET current_stock = current_stock + 100 WHERE item_id = @item_id AND branch_id = 1;
```

### Step 3: Test Sale (Stock Out)
```sql
-- Create sale
INSERT INTO sales (date, total_amount, payment_method, user_id, branch_id, status, created_at, updated_at)
VALUES ('2025-11-11', 50.00, 'cash', 1, 1, 1, NOW(), NOW());

SET @sale_id = LAST_INSERT_ID();

-- Add sale item
INSERT INTO sale_items (sale_id, item_id, item_name, quantity, unit_price, total_price, created_at, updated_at)
VALUES (@sale_id, @item_id, 'Test Product 123', 20, 2.50, 50.00, NOW(), NOW());

-- Update inventory (should be done automatically by your POS system)
UPDATE inventories SET current_stock = current_stock - 20 WHERE item_id = @item_id AND branch_id = 1;
```

### Step 4: Check Current Stock Report
- Go to: **Supervisor > Stock Report** (route: `supervisor.inventory-history`)
- Should show **80** for "Test Product 123"

### Step 5: Test Historical Stock (Current Implementation)
The current `inventoryHistory` method reconstructs historical data by:
1. Summing all productions up to the date
2. Subtracting all sales up to the date
3. Applying stock transfers
4. Applying wastages

**Current Limitation:** The existing report doesn't have an "as of date" feature in the UI.

## What You Need to Implement

To match the test plan, you need to create:

### Option 1: Add `stock_transactions` table (Recommended)
Create a unified transaction log that records all stock changes:
- Purchases → `+quantity`
- Sales → `-quantity`
- Transfers In → `+quantity`
- Transfers Out → `-quantity`
- Wastage → `-quantity`
- Production → `+quantity`

### Option 2: Enhance Current System
Add reports that query existing tables:
1. **Stock Activity Report** - Join all transaction tables with date filter
2. **Stock on Hand Report** - Calculate stock at specific date/time

## Test Results Template

| Test Step | Expected | Actual | Status |
|-----------|----------|--------|--------|
| Initial stock = 0 | ✓ | ? | ⏳ |
| After purchase +100 | 100 | ? | ⏳ |
| After sale -20 | 80 | ? | ⏳ |
| Historical (before sale) | 100 | ? | ⏳ |
| Historical (after sale) | 80 | ? | ⏳ |
| Activity report shows 2 rows | ✓ | ? | ⏳ |

## Next Steps

Would you like me to:
1. ✅ Create the missing `stock_transactions` table and migration?
2. ✅ Build the Stock Activity Report?
3. ✅ Build the Stock on Hand Report with date search?
4. ✅ Add triggers/observers to populate stock_transactions automatically?
5. ✅ Create test seeder to populate test data?

Let me know which path you'd like to take!
