# COMPLETE STOCK REPORT LOGIC EXPLANATION

## Overview
Your stock report has TWO different modes:
1. **CURRENT STOCK** (no date filter) - Shows real-time stock from database
2. **HISTORICAL STOCK** (with date filter) - Calculates stock at a specific date/time

---

## MODE 1: CURRENT STOCK REPORT (No Date Filter)

### Entry Point
```php
public function inventoryHistory(Request $request)
```

### When Used
- User visits Stock Report WITHOUT entering date/time filter
- Shows what's currently in the database right now

### Logic Flow

```
Step 1: Get all branches
  ├─ Find "Main Branch"
  └─ Get all other branches

Step 2: Call getCurrentStockData()
  ├─ Get ALL active items from database
  ├─ For EACH item:
  │   ├─ Look in 'inventories' table
  │   ├─ Get main branch stock (current_stock column)
  │   └─ Get each other branch stock (current_stock column)
  └─ Return array of items with current stock

Step 3: Display results
  └─ Show items with their REAL-TIME stock from database
```

### Data Source
```
Direct read from: inventories.current_stock
No calculations, just raw database values
```

### Example
```
Item: Bread
Main Branch inventory record: current_stock = 50
Branch A inventory record: current_stock = 20
Branch B inventory record: current_stock = 10

Result shown in report:
Main: 50, Branch A: 20, Branch B: 10
```

---

## MODE 2: HISTORICAL STOCK REPORT (With Date Filter)

### Entry Point
```php
public function inventoryHistory(Request $request)
// When $filterDate or $filterTime is provided
```

### When Used
- User enters a date (e.g., "2025-11-08")
- User enters a date + time (e.g., "2025-11-08 14:00:00")
- Shows what stock WOULD HAVE BEEN at that past date/time

### Logic Flow

```
Step 1: Build target datetime
  ├─ If date + time: use "YYYY-MM-DD HH:MM:SS"
  └─ If only date: use "YYYY-MM-DD 23:59:59"

Step 2: Get items created before target date
  ├─ Query: WHERE is_active = true
  └─ AND created_at <= target datetime

Step 3: For EACH item, call calculateStockAtDateTime()
  └─ This reconstructs stock from all transactions

Step 4: Filter results
  ├─ Remove items with NO transactions (has_activity = false)
  └─ Keep only items that had stock movement

Step 5: Display results
  └─ Show calculated historical stock
```

### The Core Calculation: calculateStockAtDateTime()

This is the HEART of historical reporting. It reconstructs stock by replaying all transactions.

```
For a specific item at a specific date/time:

Initialize:
  mainStock = 0
  branchStocks = [Branch A: 0, Branch B: 0, ...]
  lastUpdated = null

STEP 1: ADD PRODUCTIONS
  Query: inventory_requests
    WHERE status = 'completed'
    AND date_time <= target datetime
  
  For each production:
    If this item is in production:
      mainStock += quantity
      Update lastUpdated timestamp

STEP 2: APPLY STOCK TRANSFERS
  Query: stock_transfers
    WHERE status = 'accepted'
    AND date_time <= target datetime
  
  For each transfer:
    If this item is in transfer:
      
      Scenario A: From Main to Branch
        mainStock -= quantity
        branchStocks[toBranch] += quantity
      
      Scenario B: From Branch to Branch
        branchStocks[fromBranch] -= quantity
        branchStocks[toBranch] += quantity
      
      Update lastUpdated timestamp

STEP 3: SUBTRACT WASTAGES
  Query: wastages
    WHERE date_time <= target datetime
  
  For each wastage:
    If this item is in wastage:
      
      If wastage from Main:
        mainStock -= quantity
      
      If wastage from Branch:
        branchStocks[branch] -= quantity
      
      Update lastUpdated timestamp

STEP 4: SUBTRACT SALES
  Query: sales
    WHERE status = 1
    AND created_at <= target datetime
  
  For each sale:
    If this item is in sale:
      
      If sale from Main:
        mainStock -= quantity
      
      If sale from Branch:
        branchStocks[branch] -= quantity
      
      Update lastUpdated timestamp

STEP 5: VALIDATION
  Ensure no negative stocks:
    mainStock = max(0, mainStock)
    branchStocks[each] = max(0, value)

STEP 6: CHECK ACTIVITY
  hasActivity = (lastUpdated is not null)
  
  If NO transactions found:
    hasActivity = false
    Item will be filtered out

RETURN:
  main_stock: calculated value
  branch_stocks: array of calculated values
  last_updated: timestamp of last transaction
  has_activity: true/false
```

---

## DETAILED TRANSACTION PROCESSING

### 1. PRODUCTIONS (Inventory Requests)

**What it does:** Adds stock to Main Branch

**Database Query:**
```sql
SELECT * FROM inventory_requests
WHERE status = 'completed'
AND date_time <= '2025-11-08 23:59:59'
```

**Joins:**
```sql
JOIN inventory_request_items ON inventory_requests.id = inventory_request_id
WHERE item_id = [specific item]
```

**Effect:**
```
Production found: +100 units on Nov 5
Result: mainStock = 0 + 100 = 100
```

**Key Points:**
- Only 'completed' status counts
- Adds ONLY to Main Branch
- Uses date_time column for filtering

---

### 2. STOCK TRANSFERS

**What it does:** Moves stock between branches

**Database Query:**
```sql
SELECT * FROM stock_transfers
WHERE status = 'accepted'
AND date_time <= '2025-11-08 23:59:59'
```

**Joins:**
```sql
JOIN stock_transfer_items ON stock_transfers.id = transfer_id
WHERE item_id = [specific item]
```

**Two Scenarios:**

**A) From Main to Branch:**
```
Transfer: Main → Branch A, 30 units
Before: Main = 100, Branch A = 0
After:  Main = 70,  Branch A = 30
```

**B) Between Branches:**
```
Transfer: Branch A → Branch B, 10 units
Before: Branch A = 30, Branch B = 0
After:  Branch A = 20, Branch B = 10
```

**Key Points:**
- Only 'accepted' status counts
- from_branch_id null or 1 = Main Branch
- Reduces from source, adds to destination
- Uses date_time column for filtering

---

### 3. WASTAGES

**What it does:** Reduces stock due to damage/expiry

**Database Query:**
```sql
SELECT * FROM wastages
WHERE date_time <= '2025-11-08 23:59:59'
```

**Joins:**
```sql
JOIN wastage_items ON wastages.id = wastage_id
WHERE item_id = [specific item]
```

**Effect:**
```
Wastage: 5 units from Main Branch on Nov 7
Before: mainStock = 70
After:  mainStock = 65
```

**Key Points:**
- No status filter (all wastages count)
- Subtracts from the specific branch
- branch_id determines which branch
- Uses date_time column for filtering

---

### 4. SALES

**What it does:** Reduces stock due to customer sales

**Database Query:**
```sql
SELECT * FROM sales
WHERE status = 1
AND created_at <= '2025-11-08 23:59:59'
```

**Joins:**
```sql
JOIN sale_items ON sales.id = sale_id
WHERE item_id = [specific item]
```

**Effect:**
```
Sale: 20 units from Branch A on Nov 8
Before: branchStocks['Branch A'] = 30
After:  branchStocks['Branch A'] = 10
```

**Key Points:**
- Only status = 1 (active sales) count
- Subtracts from the specific branch
- branch_id determines which branch
- Uses created_at column for filtering (NOT date_time!)

---

## FILTERING LOGIC

### Item Creation Filter
```php
->where('created_at', '<=', $targetDateTime)
```

**Purpose:** Only shows items that existed at that date

**Example:**
```
Search date: Nov 8
Item A created: Nov 1  → ✅ Included
Item B created: Nov 15 → ❌ Excluded (didn't exist yet)
```

### Activity Filter
```php
->filter(function ($item) {
    return $item['has_activity'];
})
```

**Purpose:** Only shows items with transactions

**Example:**
```
Item A: Has production on Nov 5 → ✅ Included
Item B: Created Nov 3 but no transactions → ❌ Excluded
```

---

## COMPLETE CALCULATION EXAMPLE

**Scenario:**
- Item: "Chocolate Cake"
- Search Date: November 8, 2025 at 11:59 PM
- Branches: Main Branch, Branch A, Branch B

**Transaction History:**
1. Nov 1: Item created
2. Nov 5, 09:00 AM: Production +100 (Main Branch)
3. Nov 6, 02:00 PM: Transfer 30 units (Main → Branch A)
4. Nov 7, 10:00 AM: Wastage 5 units (Main Branch)
5. Nov 8, 03:00 PM: Sale 10 units (Branch A)
6. Nov 10, 08:00 AM: Transfer 20 units (Main → Branch B) ← NOT INCLUDED

**Calculation Process:**

```
1. Initialize:
   Main = 0
   Branch A = 0
   Branch B = 0

2. Add Productions (≤ Nov 8 23:59:59):
   Nov 5 production: +100
   Main = 100

3. Apply Transfers (≤ Nov 8 23:59:59):
   Nov 6 transfer: Main -30, Branch A +30
   Main = 70
   Branch A = 30

4. Subtract Wastages (≤ Nov 8 23:59:59):
   Nov 7 wastage: -5
   Main = 65

5. Subtract Sales (≤ Nov 8 23:59:59):
   Nov 8 sale: Branch A -10
   Branch A = 20

6. Final Result:
   Main = 65
   Branch A = 20
   Branch B = 0

Note: Nov 10 transfer is NOT included because 
it happened AFTER the search date (Nov 8).
```

---

## KEY DIFFERENCES BETWEEN MODES

| Aspect | Current Stock | Historical Stock |
|--------|---------------|------------------|
| **Data Source** | inventories.current_stock | Calculated from transactions |
| **Speed** | Fast (direct read) | Slower (calculations) |
| **Accuracy** | Current only | Any past date/time |
| **Shows All Items** | Yes | Only items with activity |
| **Branch Filter** | No filtering | Filters by creation date |
| **Calculation** | None | Sum of all transactions |

---

## CURRENT ISSUES / LIMITATIONS

### Issue 1: Items Without Activity Still Show in Current Report
In current mode, ALL active items show even if they have 0 stock and no transactions.

### Issue 2: Historical Report Might Miss Items
If an item was created before the date BUT had no transactions by that date, it won't appear.

**Example:**
- Item created Nov 1
- First production Nov 15
- Search for Nov 8
- Result: Item NOT shown (no activity by Nov 8)

### Issue 3: Date Column Inconsistency
- Productions: use `date_time`
- Transfers: use `date_time`
- Wastages: use `date_time`
- Sales: use `created_at` ← DIFFERENT!

This could cause timing mismatches in calculations.

---

## SUMMARY OF LOGIC

**Current Stock Report:**
```
Get all active items
→ Read inventories.current_stock for each branch
→ Display results
(Fast, simple, accurate for "right now")
```

**Historical Stock Report:**
```
Get items created before target date
→ For each item:
  → Start with 0
  → Add all productions ≤ date
  → Apply all transfers ≤ date
  → Subtract all wastages ≤ date
  → Subtract all sales ≤ date
  → Calculate final stock
→ Filter out items with no activity
→ Display results
(Slower, complex, accurate for "back then")
```

---

## WHAT DETERMINES WHAT YOU SEE

**In Current Report:**
✅ Item is active (is_active = true)
✅ Item has inventory record

**In Historical Report:**
✅ Item is active (is_active = true)
✅ Item was created before search date
✅ Item has at least ONE transaction before search date
✅ Transaction types: production, transfer, wastage, or sale

**If item doesn't appear in historical report:**
- Either created after search date
- OR has no transactions by search date
- OR all transactions happened after search date

---

This is your complete stock report logic! 

What specific issue are you seeing? Tell me:
1. What date/time are you searching?
2. Which items are missing?
3. When were those items created?
4. When did they have their first transaction?
