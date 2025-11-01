# KOT/BOT Thermal Printer Setup Guide

## Overview
This Laravel POS system now includes automatic thermal printing for:
- **KOT (Kitchen Order Ticket)** - Sent to kitchen printer
- **BOT (Bar Order Ticket)** - Sent to bar printer  
- **POS Receipt** - Sent to receipt printer

## Features
✅ Auto-print KOT/BOT/Receipt when POS sale is processed
✅ Multi-printer support (Kitchen, Bar, Receipt)
✅ Network (IP), USB, and Bluetooth printer support
✅ ESC/POS thermal printer compatible
✅ Fallback to file if printer offline
✅ Manual reprint capability
✅ Printer test functionality

## Prerequisites
- PHP 8.2+
- Laravel 12+
- ESC/POS compatible thermal printers
- Network connectivity (for IP printers)

## Installation Steps

### 1. Install Dependencies
The `mike42/escpos-php` library is already installed via Composer:
```bash
composer require mike42/escpos-php
```

### 2. Configure Environment Variables
Add these to your `.env` file:

```env
# Printer Configuration
PRINTER_ENABLED=true
PRINTER_TIMEOUT=3

# Kitchen Printer (KOT)
KOT_PRINTER_ENABLED=true
KOT_PRINTER_TYPE=network
KOT_PRINTER_CONNECTOR=192.168.1.100:9100

# Bar Printer (BOT)
BOT_PRINTER_ENABLED=true
BOT_PRINTER_TYPE=network
BOT_PRINTER_CONNECTOR=192.168.1.101:9100

# POS Receipt Printer
POS_PRINTER_ENABLED=true
POS_PRINTER_TYPE=network
POS_PRINTER_CONNECTOR=192.168.1.102:9100

# Auto Print Settings
AUTO_PRINT_KOT=true
AUTO_PRINT_BOT=true
AUTO_PRINT_RECEIPT=true

# Fallback Settings
PRINTER_FALLBACK_SAVE=true
```

### 3. Network Printer Setup

#### Step 1: Configure Static IP for Printers
1. Access your printer's network settings (usually via web interface or control panel)
2. Set a static IP address for each printer:
   - Kitchen: `192.168.1.100`
   - Bar: `192.168.1.101`
   - Receipt: `192.168.1.102`
3. Note the port (usually `9100` for ESC/POS printers)

#### Step 2: Test Network Connectivity
```bash
# Windows
ping 192.168.1.100

# Test port connectivity
telnet 192.168.1.100 9100
```

#### Step 3: Configure in Application
1. Login as Admin
2. Go to **Printer Settings** from the menu
3. Verify IP addresses and ports
4. Click **Test Printer** for each printer

### 4. USB Printer Setup (Windows)

#### For Windows:
```env
KOT_PRINTER_TYPE=windows
KOT_PRINTER_CONNECTOR="POS-80"  # Your printer name from Windows
```

#### For Linux/Mac:
```env
KOT_PRINTER_TYPE=file
KOT_PRINTER_CONNECTOR="/dev/usb/lp0"  # Your USB device path
```

## How It Works

### Auto-Print Flow
1. **POS Sale** → Items are categorized by type (Kitchen/Bar/Both)
2. **Create KOT/BOT** → Auto-generated for matching items
3. **Print Jobs** → Sent to respective printers immediately
4. **Receipt** → Printed on POS printer
5. **Fallback** → If printer fails, job saved to `storage/app/print_jobs/`

### Print Format

#### KOT Example:
```
================================
      KITCHEN ORDER
================================
KOT NO: KOT-251031-0001
Branch: Main Branch
Waiter: John Doe
Date: 31/10/2025 14:30
Sale: RCP251031-0123
--------------------------------
ITEM                        QTY
--------------------------------

Chicken Burger
    x 2
    NOTE: No onions

Pasta Carbonara
    x 1

================================
NOTES:
Extra sauce on burger
================================

    PREPARE IMMEDIATELY

```

#### BOT Example:
```
================================
        BAR ORDER
================================
BOT NO: BOT-251031-0001
Branch: Main Branch
Waiter: John Doe
Date: 31/10/2025 14:30
Sale: RCP251031-0123
--------------------------------
ITEM                        QTY
--------------------------------

Coca Cola
    x 3

Fresh Orange Juice
    x 2
    NOTE: Less ice

================================
    PREPARE IMMEDIATELY
```

## Manual Printing

### Print from KOT Index Page
1. Go to **KOT/BOT Order Tracking**
2. Click eye icon to view order
3. Use browser print (Ctrl+P) for HTML version

### Thermal Reprint via API
```javascript
// Reprint KOT to thermal printer
fetch(`/kot/${kotId}/print-thermal`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
.then(response => response.json())
.then(data => console.log(data.message));
```

## API Endpoints

### Print KOT/BOT to Thermal Printer
```
POST /kot/{id}/print-thermal
```

### Print Receipt to Thermal Printer
```
POST /pos/receipt/{id}/print
```

### Test Printer Connection
```
POST /kot/test-printer
Body: { "type": "kot" }  // or "bot", "pos"
```

## Troubleshooting

### Problem: Printer Not Found
**Solutions:**
- ✓ Check printer is powered on
- ✓ Verify IP address and port in .env
- ✓ Ping printer IP from server
- ✓ Check firewall settings
- ✓ Ensure printer is on same network

### Problem: Garbled Output
**Solutions:**
- ✓ Verify printer supports ESC/POS commands
- ✓ Check character encoding settings
- ✓ Test with official printer tools first

### Problem: Connection Timeout
**Solutions:**
- ✓ Increase `PRINTER_TIMEOUT` in .env
- ✓ Check network latency
- ✓ Verify printer port (9100 is standard)

### Problem: Nothing Prints
**Solutions:**
- ✓ Check printer has paper
- ✓ Verify printer is online (check LED indicators)
- ✓ Check print queue on printer
- ✓ Review logs in `storage/logs/laravel.log`

### Problem: Print Jobs Saved to File
**Cause:** Printer was offline or unreachable

**Solution:** 
- Check `storage/app/print_jobs/` for saved jobs
- Fix printer issue and manually reprint
- Jobs are saved with format: `kot_123_1698765432.txt`

## Advanced Configuration

### Custom Paper Width
Edit `config/printers.php`:
```php
'settings' => [
    'paper_width' => 48,  // 80mm = 48 chars, 58mm = 32 chars
]
```

### Disable Auto-Cut
```php
'settings' => [
    'cut_paper' => false,
]
```

### Enable Cash Drawer
```php
'settings' => [
    'open_drawer' => true,
]
```

## Printer Compatibility

### Tested Printers
- Epson TM-T20II
- Star TSP143III
- Bixolon SRP-350III
- Generic 80mm ESC/POS printers

### Requirements
- ESC/POS command support
- 80mm or 58mm paper width
- Network interface (for IP) or USB

## Code Structure

```
app/
├── Services/
│   └── PrinterService.php          # Main printing logic
├── Http/Controllers/
│   ├── POSController.php            # POS & Receipt printing
│   └── KotController.php            # KOT/BOT printing
config/
└── printers.php                     # Printer configuration
resources/views/
├── settings/printers.blade.php      # Admin settings page
└── kot/
    └── print.blade.php              # HTML print template
```

## Security Considerations

1. **Network Printers**: Use VLANs or private network for printers
2. **Access Control**: Only admin can change printer settings
3. **Fallback**: Sensitive data in fallback files - secure `storage/` folder
4. **Logs**: Monitor `storage/logs/` for print failures

## Performance Tips

1. **Network Latency**: Keep printers on same subnet as server
2. **Timeout**: Adjust `PRINTER_TIMEOUT` based on network speed
3. **Queue**: For high volume, consider queuing print jobs
4. **Fallback**: Clean up old fallback files periodically

## Support

For issues or questions:
1. Check `storage/logs/laravel.log` for errors
2. Test printer with manufacturer's tools
3. Verify network connectivity
4. Review this documentation

## License
Part of Ravon Restaurant POS System
