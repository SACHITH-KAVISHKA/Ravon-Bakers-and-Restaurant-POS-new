# Quick Testing Guide - Refactored Stock Report

## How to Test the New Unified Query

### Test 1: Current Stock Report (No Filter)
1. Go to: `http://your-app/supervisor/inventory-history`
2. **Don't enter any date/time filter**
3. Click "View Report" or just load the page
4. **Expected Result:**
   - Should show ALL active items
   - Stock calculated from ALL transactions up to NOW
   - Should be FAST (single query)

### Test 2: Historical Stock Report (With Date)
1. Go to: `http://your-app/supervisor/inventory-history`
2. **Enter a past date** (e.g., "2025-11-01")
3. Leave time blank (will default to 23:59:59)
4. Click "View Report"
5. **Expected Result:**
   - Should show only items created before Nov 1, 2025
   - Stock calculated from transactions up to Nov 1, 2025 23:59:59
   - Should NOT include any transactions after that date
   - Should be FAST (single query)

### Test 3: Historical Stock Report (Date + Time)
1. Go to: `http://your-app/supervisor/inventory-history`
2. **Enter date AND time** (e.g., "2025-11-08" and "14:00:00")
3. Click "View Report"
4. **Expected Result:**
   - Should show stock as of Nov 8, 2025 at 2:00 PM
   - Transactions after 2:00 PM should NOT be included
   - Should be FAST (single query)

### Test 4: Excel Export
1. Go to: `http://your-app/supervisor/inventory-history`
2. Enter a date filter (or leave blank for current)
3. Click "Export to Excel"
4. **Expected Result:**
   - Should download .xlsx file
   - Should contain same data as on-screen report
   - Should be FAST

### Test 5: Items with No Transactions
1. Create a new item in the system
2. **Don't add any transactions for it** (no productions, transfers, sales, etc.)
3. View current stock report
4. **Expected Result:**
   - Item should appear in list
   - All branches should show 0 stock
   - This is CORRECT (item exists but has no stock)

### Test 6: Items Created After Filter Date
1. Note today's date
2. View historical report for a date in the PAST (e.g., 1 month ago)
3. **Expected Result:**
   - Items created TODAY should NOT appear in the report
   - Only items that existed at the filter date should appear

### Test 7: Branch Stock Distribution
1. Create test scenario:
   - Production: +100 to Main Branch
   - Transfer: -30 from Main to Branch A
   - Transfer: -20 from Main to Branch B
   - Sale: -10 from Branch A
2. View current stock report
3. **Expected Result:**
   - Main Branch: 100 - 30 - 20 = **50**
   - Branch A: 30 - 10 = **20**
   - Branch B: **20**
   - Total: 50 + 20 + 20 = **90** ✅

### Test 8: Performance Check
1. Open browser DevTools (F12)
2. Go to Network tab
3. Load stock report page
4. **Check:**
   - Total page load time (should be fast, <2 seconds)
   - Number of database queries (Laravel Debugbar if installed)
   - With old code: 400+ queries
   - With new code: **1 query** ✅

---

## Common Issues and Fixes

### Issue: "SQLSTATE[42S22]: Column not found"
**Cause:** Database column name mismatch  
**Fix:** Check that your tables have the correct columns:
- `inventory_requests.date_time` ✅
- `stock_transfers.date_time` ✅
- `wastages.date_time` ✅
- `sales.created_at` ✅ (note: different from others!)

### Issue: "Items missing from report"
**Cause:** Items created after the filter date  
**Fix:** This is CORRECT behavior. Only items that existed at the filter date should appear.

### Issue: "Stock showing as 0 for all branches"
**Cause:** No transactions found, or transactions after filter date  
**Fix:** 
1. Check if item has any transactions
2. Check if transactions are before the filter date
3. Verify transaction status (completed, accepted, status=1)

### Issue: "Main branch stock incorrect"
**Cause:** Main branch ID not detected correctly  
**Fix:** Verify main branch exists and has name 'Main Branch' or is the first active branch

### Issue: "Negative stock appearing"
**Cause:** Database has bad data (more outgoing than incoming)  
**Fix:** The query uses `max(0, stock)` to prevent negatives. If showing negative, check the SQL query.

---

## SQL Query Testing (Direct Database)

If you want to test the SQL query directly:

```sql
-- Replace these values:
SET @main_branch_id = 1;
SET @target_datetime = '2025-11-08 23:59:59';

-- Run the unified query:
SELECT
    items.id as item_id,
    items.item_name as item_name,
    items.item_code as item_code,
    branches.id as branch_id,
    branches.name as branch_name,
    COALESCE(SUM(transactions.quantity_change), 0) as calculated_stock
FROM
    items
CROSS JOIN
    branches
LEFT JOIN (
    /* Productions */
    SELECT
        item_id,
        @main_branch_id as branch_id,
        quantity as quantity_change,
        date_time as transaction_date
    FROM
        inventory_requests
    JOIN
        inventory_request_items ON inventory_requests.id = inventory_request_items.inventory_request_id
    WHERE
        status = 'completed'

    UNION ALL

    /* Transfers Out */
    SELECT
        item_id,
        COALESCE(from_branch_id, @main_branch_id) as branch_id,
        -quantity as quantity_change,
        date_time as transaction_date
    FROM
        stock_transfers
    JOIN
        stock_transfer_items ON stock_transfers.id = stock_transfer_items.transfer_id
    WHERE
        status = 'accepted'

    UNION ALL

    /* Transfers In */
    SELECT
        item_id,
        to_branch_id as branch_id,
        quantity as quantity_change,
        date_time as transaction_date
    FROM
        stock_transfers
    JOIN
        stock_transfer_items ON stock_transfers.id = stock_transfer_items.transfer_id
    WHERE
        status = 'accepted'

    UNION ALL

    /* Wastages */
    SELECT
        item_id,
        branch_id,
        -quantity as quantity_change,
        date_time as transaction_date
    FROM
        wastages
    JOIN
        wastage_items ON wastages.id = wastage_items.wastage_id

    UNION ALL

    /* Sales */
    SELECT
        item_id,
        branch_id,
        -quantity as quantity_change,
        created_at as transaction_date
    FROM
        sales
    JOIN
        sale_items ON sales.id = sale_items.sale_id
    WHERE
        status = 1

) AS transactions
    ON items.id = transactions.item_id
    AND branches.id = transactions.branch_id
    AND transactions.transaction_date <= @target_datetime

WHERE
    items.is_active = 1
    AND items.created_at <= @target_datetime
    AND branches.status = 1

GROUP BY
    items.id, items.item_name, items.item_code, branches.id, branches.name
ORDER BY
    items.item_name, branches.id;
```

---

## Checklist

- [ ] Current stock report loads correctly
- [ ] Historical stock report with date works
- [ ] Historical stock report with date+time works
- [ ] Excel export works for both modes
- [ ] Items with no transactions show 0 stock
- [ ] Items created after filter date don't appear
- [ ] Branch stock distribution is correct
- [ ] Page loads FAST (1-2 seconds max)
- [ ] No SQL errors in Laravel log
- [ ] Stock values match manual calculations

---

**If all tests pass: The refactoring is successful! ✅**

**If tests fail: Check the "Common Issues" section above or examine `storage/logs/laravel.log`**
