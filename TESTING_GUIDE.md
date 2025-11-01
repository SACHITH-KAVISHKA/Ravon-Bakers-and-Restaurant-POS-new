# Testing BOT Printer - Step by Step Guide

## Method 1: Use the Built-in Test Page (EASIEST)

### Steps:
1. Open your browser
2. Go to: `http://localhost/kot/printer-test` (or your domain)
3. You'll see a page with printer test buttons
4. Click **"Test Bar Printer"** button
5. Check your BOT printer (192.168.1.14) for output

**Expected Result:**
- Success message appears
- Thermal printer prints a test ticket
- Test ticket shows: "TEST BAR ORDER" with sample items

---

## Method 2: Test Network Connection First

### Step 1: Check if printer is reachable
Open PowerShell and run:

```powershell
# Test if printer IP is reachable
ping 192.168.1.14
```

**Expected Output:**
```
Reply from 192.168.1.14: bytes=32 time<1ms TTL=64
Reply from 192.168.1.14: bytes=32 time<1ms TTL=64
```

✅ If you see replies = Printer is online
❌ If you see "Request timed out" = Check printer power/network

### Step 2: Check if port 9100 is open
```powershell
# Test if port 9100 is accessible
Test-NetConnection -ComputerName 192.168.1.14 -Port 9100
```

**Expected Output:**
```
TcpTestSucceeded : True
```

✅ If True = Port is open, printer ready
❌ If False = Check printer configuration

---

## Method 3: Test with Real Sale (RECOMMENDED)

### Steps:
1. **Open POS System**
   - Go to: `http://localhost/pos` (or your POS route)

2. **Add Beverages to Cart**
   - Click on beverage items (Coca Cola, Juice, etc.)
   - Items should be marked as "Bar" type in your database

3. **Process Payment**
   - Click "Process Payment"
   - Select payment method (Cash/Card)
   - Click "Confirm Payment"

4. **Watch for Results**
   - Success message: "Payment Successful! KOT/BOT sent to printers."
   - NO pop-up windows should appear
   - Check your BOT printer (192.168.1.14)
   - Thermal ticket should print within 1-2 seconds

**What Should Print:**
```
================================
        BAR ORDER
================================
BOT NO: BOT-251101-0001
Branch: [Your Branch]
Waiter: [Staff Name]
Date: 01/11/2025 14:30
Sale: RCP251101-0123
--------------------------------
ITEM                        QTY
--------------------------------

Coca Cola
    x 2

Orange Juice
    x 1

================================
    PREPARE IMMEDIATELY
```

---

## Method 4: Check Logs (Troubleshooting)

### View Real-time Logs
```powershell
# Open PowerShell in project directory
cd "c:\Users\Campus\Desktop\New Ravon Project"

# Watch logs in real-time
Get-Content storage/logs/laravel.log -Wait -Tail 20
```

### What to Look For:

✅ **SUCCESS - Look for:**
```
[2025-11-01 14:30:15] local.INFO: BOT printed successfully: BOT-251101-0001
```

❌ **ERROR - Look for:**
```
[2025-11-01 14:30:15] local.ERROR: Failed to print BOT: Connection timeout
```

---

## Method 5: Check Fallback Files

If printer is offline, the system saves print jobs to files.

### Location:
```
c:\Users\Campus\Desktop\New Ravon Project\storage\app\print_jobs\
```

### Check Files:
```powershell
# List print job files
Get-ChildItem "storage\app\print_jobs\" | Select-Object Name, LastWriteTime
```

**If you see files here:**
- Printer was offline when print was attempted
- System saved the job for manual reprint
- You can open these files to see what should have printed

---

## Verification Checklist

### ✅ Before Testing:

- [ ] Printer is powered ON
- [ ] Printer has paper loaded
- [ ] Printer network cable is connected
- [ ] Printer IP is 192.168.1.14 (check printer display/label)
- [ ] Your laptop is on same network as printer
- [ ] Firewall allows connection to port 9100

### ✅ Configuration Check:

```powershell
# Check .env file
Get-Content .env | Select-String "BOT"
```

**Should show:**
```
BOT_PRINTER_ENABLED=true
BOT_PRINTER_TYPE=network
BOT_PRINTER_CONNECTOR=192.168.1.14:9100
AUTO_PRINT_BOT=true
```

### ✅ After running config:clear:

```powershell
php artisan config:clear
```

---

## Common Issues & Solutions

### Issue 1: "Connection Timeout"

**Cause:** Cannot reach printer at 192.168.1.14

**Solutions:**
1. Verify printer IP with ping test
2. Check network cable
3. Verify both devices on same network
4. Check Windows Firewall
5. Restart printer

### Issue 2: "No Print Output"

**Cause:** Printer connected but not printing

**Solutions:**
1. Check printer has paper
2. Check printer is in "Ready" state (not offline/error)
3. Try printer self-test (usually hold FEED button on power-up)
4. Verify printer supports ESC/POS commands

### Issue 3: "Pop-up Windows Still Appear"

**Cause:** Browser cache

**Solutions:**
1. Hard refresh: `Ctrl + Shift + R`
2. Clear browser cache
3. Run: `php artisan config:clear`
4. Check pos/index.blade.php has no window.open() code

### Issue 4: "Print Jobs in Fallback Folder"

**Cause:** Printer offline when printing attempted

**Solutions:**
1. Check printer is online now
2. Use test page to verify connection
3. Reprocess the order
4. Files saved in: `storage/app/print_jobs/`

---

## Quick Test Script

Save this as `test-printer.ps1`:

```powershell
# Quick Printer Test Script
Write-Host "=== BOT Printer Test ===" -ForegroundColor Cyan

# Test 1: Network connectivity
Write-Host "`n1. Testing network connection..." -ForegroundColor Yellow
$ping = Test-Connection -ComputerName 192.168.1.14 -Count 2 -Quiet
if ($ping) {
    Write-Host "   ✅ Printer is reachable" -ForegroundColor Green
} else {
    Write-Host "   ❌ Cannot reach printer" -ForegroundColor Red
    exit
}

# Test 2: Port check
Write-Host "`n2. Testing port 9100..." -ForegroundColor Yellow
$port = Test-NetConnection -ComputerName 192.168.1.14 -Port 9100 -WarningAction SilentlyContinue
if ($port.TcpTestSucceeded) {
    Write-Host "   ✅ Port 9100 is open" -ForegroundColor Green
} else {
    Write-Host "   ❌ Port 9100 is closed" -ForegroundColor Red
}

# Test 3: Configuration check
Write-Host "`n3. Checking configuration..." -ForegroundColor Yellow
$config = Get-Content .env | Select-String "BOT_PRINTER_CONNECTOR"
Write-Host "   $config"

Write-Host "`n=== Test Complete ===" -ForegroundColor Cyan
Write-Host "Next: Visit http://localhost/kot/printer-test" -ForegroundColor Yellow
```

**Run with:**
```powershell
.\test-printer.ps1
```

---

## Expected Flow (Visual)

```
User Action → System Response → Printer Output
───────────────────────────────────────────────

1. Add beverages to cart
   └─> Items tagged as "Bar" type

2. Click "Process Payment"
   └─> POSController processes sale
   
3. Confirm payment
   └─> DB Transaction:
       ├─> Sale created
       ├─> BOT created
       └─> PrinterService::printBOT() called
   
4. Backend connects to printer
   └─> TCP socket to 192.168.1.14:9100
   
5. ESC/POS commands sent
   └─> Thermal printer receives data
   
6. 🖨️ TICKET PRINTS
   └─> Success message shown
   
7. No pop-ups appear
   └─> Clean user experience
```

---

## Success Indicators

### ✅ System Working Correctly When:

1. Success message shows: **"Payment Successful! KOT/BOT sent to printers."**
2. NO browser windows pop up
3. Thermal printer prints within 1-2 seconds
4. Log shows: `BOT printed successfully: BOT-XXXXXX-XXXX`
5. No files in `storage/app/print_jobs/`

### ❌ Check Configuration When:

1. Browser pop-up windows appear
2. No thermal print output
3. Error messages in logs
4. Timeout errors
5. Files accumulate in `storage/app/print_jobs/`

---

## RECOMMENDED TESTING ORDER:

1. ✅ **Network Test** (ping 192.168.1.14)
2. ✅ **Port Test** (Test-NetConnection)
3. ✅ **Config Check** (verify .env)
4. ✅ **Test Page** (http://localhost/kot/printer-test)
5. ✅ **Real Sale** (Process beverage order)
6. ✅ **Log Check** (tail logs for success/errors)

---

## Need Help?

**Check logs first:**
```powershell
Get-Content storage/logs/laravel.log -Tail 50
```

**Clear everything and retry:**
```powershell
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

**Test printer manually:**
Visit: `http://localhost/kot/printer-test`
