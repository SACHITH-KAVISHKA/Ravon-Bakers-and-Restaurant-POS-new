# 🚀 Quick Start Guide - Thermal Printer Setup

## Step 1: Configure Your Printers (5 minutes)

### Update .env File
Open `.env` and update these lines with your printer IPs:

```env
# Kitchen Printer (KOT)
KOT_PRINTER_CONNECTOR=192.168.1.100:9100

# Bar Printer (BOT)
BOT_PRINTER_CONNECTOR=192.168.1.101:9100

# POS Receipt Printer
POS_PRINTER_CONNECTOR=192.168.1.102:9100
```

**Replace the IP addresses above with your actual printer IPs!**

## Step 2: Test Printers (2 minutes)

1. Login to your Laravel application as **Admin**
2. Click **Printer Settings** in the sidebar
3. Click **Test Kitchen Printer** button
4. Click **Test Bar Printer** button
5. Click **Test POS Printer** button

✅ If test prints appear, you're ready to go!

## Step 3: Process a Sale (1 minute)

1. Go to **POS System**
2. Add items to cart:
   - Add kitchen items (food)
   - Add bar items (drinks)
3. Click **Process Payment**
4. Complete the payment

**Result:**
- ✅ KOT prints automatically on kitchen printer
- ✅ BOT prints automatically on bar printer
- ✅ Receipt can be printed on POS printer

## Done! 🎉

Your thermal printers are now integrated and working!

---

## Troubleshooting

### Printers Not Found?

**Quick Fixes:**
1. Ping your printer: `ping 192.168.1.100`
2. Check printer is powered on
3. Verify IP address in printer settings
4. Ensure printer is on same network

### Still Not Working?

Check fallback files: `storage/app/print_jobs/`

If files are being created there, your print jobs are being saved but printers are offline.

---

## Need Help?

📖 **Full Documentation:** See `PRINTER_SETUP.md`

📋 **Implementation Details:** See `IMPLEMENTATION_SUMMARY.md`

🔍 **Check Logs:** `storage/logs/laravel.log`

---

**Default Printer IPs to Configure:**
- Kitchen: 192.168.1.100:9100
- Bar: 192.168.1.101:9100  
- POS: 192.168.1.102:9100

**Change these in your .env file to match your actual printer IPs!**
