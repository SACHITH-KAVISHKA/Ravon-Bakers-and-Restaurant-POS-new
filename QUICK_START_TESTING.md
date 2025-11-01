# Quick Start Guide - KOT/BOT Auto-Print Testing

## Prerequisites Checklist

✅ **Database Migrations Run**
```bash
php artisan migrate
```

✅ **Items Have Type Configured**
```sql
-- Quick check
SELECT item_name, item_type FROM items LIMIT 10;

-- If NULL, set defaults
UPDATE items SET item_type = 'Kitchen' WHERE item_type IS NULL;
```

---

## Testing Steps

### Step 1: Configure Test Items (5 minutes)

**Option A: Via Database (Recommended)**
```sql
-- Set a few items for testing
UPDATE items SET item_type = 'Kitchen' WHERE item_name LIKE '%Pizza%';
UPDATE items SET item_type = 'Kitchen' WHERE item_name LIKE '%Burger%';
UPDATE items SET item_type = 'Bar' WHERE item_name LIKE '%Beer%';
UPDATE items SET item_type = 'Bar' WHERE item_name LIKE '%Drink%';
UPDATE items SET item_type = 'Both' WHERE item_name LIKE '%Coffee%';
```

**Option B: Via Laravel Tinker**
```bash
php artisan tinker

# In tinker
use App\Models\Item;

Item::where('item_name', 'like', '%Pizza%')->update(['item_type' => 'Kitchen']);
Item::where('item_name', 'like', '%Beer%')->update(['item_type' => 'Bar']);
Item::where('item_name', 'like', '%Coffee%')->update(['item_type' => 'Both']);
```

### Step 2: Verify Routes (30 seconds)
```bash
php artisan route:list | findstr "kot"
```

**Expected Output:**
```
kot.index       GET    /kot
kot.create      GET    /kot/create
kot.store       POST   /kot
kot.show        GET    /kot/{kot}
kot.print       GET    /kot/{kot}/print
kot.kitchen     GET    /kot/kitchen
...
```

### Step 3: Start Laravel Server (if not running)
```bash
php artisan serve
```

### Step 4: Configure Browser for Testing

**Chrome/Edge:**
1. Go to Settings → Privacy and Security → Site Settings → Pop-ups and redirects
2. Add your POS URL to "Allowed to send pop-ups"
   - Example: `http://127.0.0.1:8000`

### Step 5: Make a Test Sale

1. **Open POS**: http://127.0.0.1:8000/pos

2. **Add Items to Cart**:
   - Add at least 1 Kitchen item (e.g., Pizza)
   - Add at least 1 Bar item (e.g., Beer)
   - Add at least 1 Both item (e.g., Coffee) - optional

3. **Complete Sale**:
   - Click "Payment" or "Checkout"
   - Enter payment amount
   - Click "Print Receipt" or "Complete Sale"

4. **Expected Result**:
   - ✅ Payment modal closes
   - ✅ POS receipt PDF downloads
   - ✅ KOT print window opens (after 500ms)
   - ✅ BOT print window opens (after 1000ms)

### Step 6: Verify in Database

```sql
-- Check if KOTs were created
SELECT * FROM kots ORDER BY created_at DESC LIMIT 5;

-- Check KOT items
SELECT 
    k.kot_no,
    k.type,
    ki.item_name,
    ki.quantity
FROM kots k
JOIN kot_items ki ON k.id = ki.kot_id
ORDER BY k.created_at DESC
LIMIT 10;
```

---

## Expected Behavior

### Scenario 1: Sale with Kitchen Items Only
**Cart**: Pizza (2), Burger (1)

**What Happens**:
- ✅ 1 POS Receipt prints
- ✅ 1 KOT prints (with Pizza + Burger)
- ❌ No BOT prints

### Scenario 2: Sale with Bar Items Only
**Cart**: Beer (3), Cocktail (1)

**What Happens**:
- ✅ 1 POS Receipt prints
- ❌ No KOT prints
- ✅ 1 BOT prints (with Beer + Cocktail)

### Scenario 3: Sale with Mixed Items
**Cart**: Pizza (2), Beer (3), Coffee (1)

**What Happens**:
- ✅ 1 POS Receipt prints
- ✅ 1 KOT prints (with Pizza + Coffee)
- ✅ 1 BOT prints (with Beer + Coffee)

**Note**: Coffee appears on BOTH tickets because item_type = 'Both'

### Scenario 4: Sale with No Kitchen/Bar Items
**Cart**: Generic Product (no item_type or NULL)

**What Happens**:
- ✅ 1 POS Receipt prints
- ❌ No KOT prints
- ❌ No BOT prints

---

## Troubleshooting Common Issues

### Issue 1: Pop-ups Blocked
**Symptom**: No KOT/BOT windows open

**Solution**:
1. Check browser address bar for blocked pop-up icon
2. Click and allow pop-ups
3. Retry the sale

### Issue 2: No KOT/BOT Created
**Check 1**: Items have item_type set
```sql
SELECT item_name, item_type FROM items;
```

**Check 2**: Browser console for errors
- Press F12
- Go to Console tab
- Look for red errors

**Check 3**: Laravel logs
```bash
# Windows PowerShell
Get-Content "storage\logs\laravel.log" -Tail 50
```

### Issue 3: KOT/BOT Print Page is Blank
**Check**: Route exists and controller method works
```bash
php artisan route:list | findstr "kot.print"
```

**Test Direct URL**: http://127.0.0.1:8000/kot/1/print
- Should show a printable KOT ticket

### Issue 4: Wrong Items on KOT/BOT
**Check Item Types**:
```sql
-- See what type each item has
SELECT id, item_name, item_type FROM items ORDER BY item_name;

-- Fix incorrect types
UPDATE items SET item_type = 'Kitchen' WHERE item_name = 'Pizza';
UPDATE items SET item_type = 'Bar' WHERE item_name = 'Beer';
```

### Issue 5: Print Windows Open But Nothing Prints
**Printer Configuration**:
1. Check printer is selected in print dialog
2. Check printer is online and has paper
3. Check print preview shows content

### Issue 6: Session Errors
**Clear Session**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Quick Verification Checklist

Before reporting issues, verify:

- [ ] Migrations run: `php artisan migrate:status`
- [ ] Items have item_type: `SELECT COUNT(*) FROM items WHERE item_type IS NULL;` (should be 0)
- [ ] Routes exist: `php artisan route:list | findstr kot`
- [ ] Browser allows pop-ups
- [ ] Laravel is running: `php artisan serve`
- [ ] No errors in browser console (F12)
- [ ] No errors in Laravel logs (`storage/logs/laravel.log`)

---

## Test Data Setup (Optional)

If you want clean test data:

```sql
-- Create test items
INSERT INTO items (item_name, item_code, category, item_type, is_active) VALUES
('TEST Pizza', 'TEST-PIZZA', 'Food', 'Kitchen', 1),
('TEST Beer', 'TEST-BEER', 'Drinks', 'Bar', 1),
('TEST Coffee', 'TEST-COFFEE', 'Drinks', 'Both', 1);

-- Create test item prices (if using branch prices)
INSERT INTO item_branch_prices (item_id, branch_id, price, effective_date)
SELECT id, 1, 500.00, NOW() FROM items WHERE item_code LIKE 'TEST-%';
```

---

## Testing Video Walkthrough

**What to Record**:
1. Open POS
2. Add 1 Kitchen item
3. Add 1 Bar item
4. Complete sale
5. Show 3 windows opening:
   - POS Receipt PDF
   - KOT Print Window
   - BOT Print Window

**Share with team** to show how it works!

---

## Performance Testing

### Test Load
```bash
# Test with many items
# Add 10 kitchen items + 10 bar items to one sale
# Verify all items appear correctly on KOT/BOT
```

### Test Speed
- Sale completion should be < 2 seconds
- Print windows should open within 1-2 seconds
- No lag or freezing

---

## Success Criteria

✅ **Test Passed When**:
1. Sale completes successfully
2. POS receipt PDF downloads
3. KOT window opens (if kitchen items present)
4. BOT window opens (if bar items present)
5. Correct items appear on each ticket
6. Ticket numbers are unique and sequential
7. No errors in console or logs

---

## Next Steps After Testing

Once testing is successful:

1. **Configure All Items**:
   - Run `setup_kot_bot_items.sql` with your actual items
   - Verify all items have correct types

2. **Train Staff**:
   - Show them the auto-print feature
   - Explain they'll see 2-3 print windows
   - Show how to handle print dialogs

3. **Configure Printers**:
   - Set up thermal printers for kitchen and bar
   - Test printing from each location
   - Configure auto-print if desired

4. **Go Live**:
   - Monitor first few sales closely
   - Check tickets are printing correctly
   - Gather feedback from kitchen/bar staff

---

**Ready to Test?**

1. Run: `php artisan serve`
2. Open: http://127.0.0.1:8000/pos
3. Make a sale with mixed items
4. Watch the magic happen! 🎉

---

**Questions?** Check `KOT_BOT_AUTO_PRINT_IMPLEMENTATION.md` for detailed documentation.
