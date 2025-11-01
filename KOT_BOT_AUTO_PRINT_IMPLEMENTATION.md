# KOT/BOT Auto-Print Implementation - Complete Guide

## Overview
This implementation automatically generates and prints Kitchen Order Tickets (KOT) and Bar Order Tickets (BOT) when a sale is completed in the POS system. No manual order creation or display screens are needed.

---

## How It Works

### 1. **POS Sale Processing Flow**
When a sale is completed in the POS system:

1. **Sale Creation**: The system creates a sale record with all items
2. **Item Classification**: Each item is checked for its `item_type`:
   - **Kitchen**: Item goes to KOT (Kitchen Order Ticket)
   - **Bar**: Item goes to BOT (Bar Order Ticket)
   - **Both**: Item goes to BOTH KOT and BOT
3. **Auto KOT/BOT Generation**: 
   - If there are Kitchen items → Automatically creates a KOT
   - If there are Bar items → Automatically creates a BOT
4. **Auto-Print**: Print windows automatically open for:
   - POS Receipt (always)
   - KOT (if kitchen items exist)
   - BOT (if bar items exist)

### 2. **KOT/BOT Numbering System**
- **Format**: `PREFIX-YYYYMMDD-XXXX`
- **Examples**:
  - `KOT-20250131-0001` (First KOT of the day)
  - `BOT-20250131-0001` (First BOT of the day)
- **Daily Reset**: Numbers reset to 0001 each day
- **Auto-Increment**: Numbers increase throughout the day

---

## Database Structure

### Items Table
```sql
items:
  - id (primary key)
  - item_name
  - item_code
  - category
  - item_type (ENUM: 'Kitchen', 'Bar', 'Both')  ← NEW FIELD
  - description
  - is_active
```

### KOTs Table
```sql
kots:
  - id (primary key)
  - kot_no (unique KOT/BOT number)
  - type ('KOT' or 'BOT')
  - sale_id (links to sales table)
  - branch_id
  - user_id
  - table_no (nullable)
  - notes
  - status (Pending/Preparing/Ready/Served/Completed)
  - created_at
  - updated_at
```

### KOT Items Table
```sql
kot_items:
  - id (primary key)
  - kot_id (foreign key to kots)
  - item_id (foreign key to items)
  - item_name
  - quantity
  - unit_price
  - total_price
  - notes
  - created_at
  - updated_at
```

---

## Code Changes Made

### 1. POSController.php
**Location**: `app/Http/Controllers/POSController.php`

**Changes**:
1. Added model imports:
   ```php
   use App\Models\Kot;
   use App\Models\KotItem;
   ```

2. Modified `processSale()` method to:
   - Separate items by type (Kitchen/Bar/Both)
   - Auto-create KOT for kitchen items
   - Auto-create BOT for bar items
   - Return print URLs in JSON response

3. Added new private method `createKot()`:
   - Generates KOT/BOT number
   - Creates KOT/BOT record
   - Adds items to the ticket
   - Stores ticket ID in session for printing

4. Updated `clearSession()` to clear KOT/BOT session data

### 2. POS Frontend (index.blade.php)
**Location**: `resources/views/pos/index.blade.php`

**Changes**:
- Updated AJAX success handler to auto-open print windows
- Opens KOT print window (500ms delay)
- Opens BOT print window (1000ms delay)
- Opens windows sequentially to avoid pop-up blocking

---

## Setting Item Types

### Via Database (Quick Setup)
```sql
-- Set items as Kitchen type
UPDATE items SET item_type = 'Kitchen' 
WHERE item_name IN ('Pizza', 'Burger', 'Pasta', 'Salad');

-- Set items as Bar type
UPDATE items SET item_type = 'Bar' 
WHERE item_name IN ('Beer', 'Wine', 'Cocktail', 'Soft Drink');

-- Set items as Both (goes to kitchen AND bar)
UPDATE items SET item_type = 'Both' 
WHERE item_name IN ('Coffee', 'Tea', 'Milkshake');
```

### Via Admin Panel (Future Enhancement)
You can add a dropdown in the item create/edit form:
```html
<select name="item_type" required>
    <option value="Kitchen">Kitchen</option>
    <option value="Bar">Bar</option>
    <option value="Both">Both</option>
</select>
```

---

## Print Workflow

### What Prints Automatically:

1. **POS Receipt** (always prints)
   - Customer receipt with all items
   - Payment details
   - Change/balance
   
2. **Kitchen Order Ticket (KOT)** (if kitchen items exist)
   - Only kitchen items
   - Ticket number (e.g., KOT-20250131-0001)
   - Quantities and item names
   - Special notes if any
   - Timestamp
   
3. **Bar Order Ticket (BOT)** (if bar items exist)
   - Only bar items
   - Ticket number (e.g., BOT-20250131-0001)
   - Quantities and item names
   - Special notes if any
   - Timestamp

### Print Timing:
- **Receipt**: Opens immediately
- **KOT**: Opens 500ms after receipt
- **BOT**: Opens 1000ms after receipt
- **Reason**: Staggered timing prevents browser pop-up blocking

---

## Example Scenario

### Sale Example:
**Cart Items**:
1. Pizza (Qty: 2) - Item Type: **Kitchen**
2. Beer (Qty: 3) - Item Type: **Bar**
3. Coffee (Qty: 1) - Item Type: **Both**
4. Burger (Qty: 1) - Item Type: **Kitchen**

### What Gets Printed:

#### 1. POS Receipt
```
=============================
      RAVON BAKERS
=============================
Date: 2025-01-31 14:30:25
Receipt: REC-20250131-0123
-----------------------------
Pizza x2          Rs. 2,000
Beer x3           Rs. 1,500
Coffee x1         Rs. 200
Burger x1         Rs. 500
-----------------------------
TOTAL:            Rs. 4,200
PAID:             Rs. 5,000
CHANGE:           Rs. 800
=============================
```

#### 2. Kitchen Order Ticket (KOT-20250131-0045)
```
=============================
   KITCHEN ORDER TICKET
=============================
KOT No: KOT-20250131-0045
Time: 2025-01-31 14:30:25
Sale: REC-20250131-0123
-----------------------------
Pizza x2
Burger x1
Coffee x1  ← (Both type)
-----------------------------
Status: PENDING
=============================
```

#### 3. Bar Order Ticket (BOT-20250131-0032)
```
=============================
    BAR ORDER TICKET
=============================
BOT No: BOT-20250131-0032
Time: 2025-01-31 14:30:25
Sale: REC-20250131-0123
-----------------------------
Beer x3
Coffee x1  ← (Both type)
-----------------------------
Status: PENDING
=============================
```

---

## Testing the System

### 1. Verify Item Types
```sql
-- Check which items have item_type set
SELECT id, item_name, item_type FROM items;

-- If item_type is NULL, set default
UPDATE items SET item_type = 'Kitchen' WHERE item_type IS NULL;
```

### 2. Make a Test Sale
1. Open POS: `/pos`
2. Add items to cart:
   - At least one Kitchen item
   - At least one Bar item
3. Complete the sale
4. Verify:
   - ✅ POS receipt opens
   - ✅ KOT print window opens (500ms delay)
   - ✅ BOT print window opens (1000ms delay)

### 3. Check Database
```sql
-- View created KOTs/BOTs
SELECT * FROM kots ORDER BY created_at DESC LIMIT 10;

-- View KOT items
SELECT ki.*, i.item_name 
FROM kot_items ki 
JOIN items i ON ki.item_id = i.id 
ORDER BY ki.created_at DESC 
LIMIT 20;
```

---

## Printer Configuration

### For Thermal Printers:

1. **Browser Settings**:
   - Allow pop-ups from your POS domain
   - Set default printer for receipt printing
   
2. **Print Dialog**:
   - The print dialog will open automatically
   - Select your thermal printer
   - Click "Print"
   
3. **Auto-Print (Advanced)**:
   - Configure browser to auto-print without dialog
   - Chrome: Settings → Advanced → Printing → Auto-print
   - Set different printers for different windows

### Recommended Setup:
- **Main Counter**: POS receipt printer
- **Kitchen**: KOT thermal printer
- **Bar Counter**: BOT thermal printer

---

## Kitchen/Bar Workflow

### Kitchen Staff:
1. **KOT Arrives**: Printed ticket appears
2. **Start Cooking**: Update status to "Preparing" (optional)
3. **Food Ready**: Update status to "Ready" (optional)
4. **Served**: Update status to "Served" (optional)

### Bar Staff:
1. **BOT Arrives**: Printed ticket appears
2. **Start Making**: Update status to "Preparing" (optional)
3. **Drinks Ready**: Update status to "Ready" (optional)
4. **Served**: Update status to "Served" (optional)

### Status Updates (Optional):
- Can be done via Kitchen Display screen: `/kot/kitchen`
- Or keep it simple and just use printed tickets

---

## Troubleshooting

### Problem: Pop-ups Blocked
**Solution**: Allow pop-ups for your POS domain in browser settings

### Problem: No KOT/BOT Printing
**Check**:
1. Items have `item_type` set (not NULL)
2. Browser console for errors
3. Verify routes exist: `php artisan route:list | grep kot.print`

### Problem: Wrong Items on KOT/BOT
**Check**:
```sql
-- Verify item types
SELECT item_name, item_type FROM items;

-- Fix item types if needed
UPDATE items SET item_type = 'Kitchen' WHERE item_name = 'Pizza';
UPDATE items SET item_type = 'Bar' WHERE item_name = 'Beer';
```

### Problem: Duplicate KOT Numbers
**Check**: Database unique constraint on `kot_no` field
```sql
ALTER TABLE kots ADD UNIQUE KEY unique_kot_no (kot_no);
```

---

## Future Enhancements

### Optional Features You Can Add:

1. **Silent Auto-Print**:
   - Configure printer to auto-print without dialog
   - Requires browser extension or custom print server

2. **Kitchen Display System**:
   - Keep `/kot/kitchen` route for large screen display
   - Shows all pending orders in real-time
   - Auto-refreshes every 30 seconds

3. **Order Status Tracking**:
   - Add buttons to update order status
   - Track preparation times
   - Generate reports on order completion

4. **Multi-Printer Support**:
   - Different printers for different item categories
   - Network printer configuration
   - Print queue management

5. **Item Customization**:
   - Add notes to items (e.g., "No onions")
   - Pass notes to KOT/BOT
   - Display on printed tickets

---

## Summary

✅ **What Was Implemented**:
- Auto KOT/BOT generation during POS sales
- Automatic print window opening
- No manual order creation needed
- No display screens required (optional)
- Seamless integration with existing POS workflow

✅ **What Happens Now**:
- POS sale → Auto creates KOT/BOT → Auto prints → Kitchen/Bar gets tickets

✅ **No User Intervention Required**:
- Everything happens automatically
- Staff just needs to click "Print" on each print dialog
- Or configure auto-print for fully automated workflow

---

## Support

If you encounter any issues:
1. Check this guide's troubleshooting section
2. Verify database migrations: `php artisan migrate:status`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify routes: `php artisan route:list | grep kot`

---

**Implementation Date**: January 31, 2025  
**Version**: 1.0  
**Status**: ✅ Complete and Ready for Production
