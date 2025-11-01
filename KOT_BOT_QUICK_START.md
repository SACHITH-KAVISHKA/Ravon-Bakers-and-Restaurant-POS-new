# KOT/BOT Quick Start Guide

## 🎉 Your KOT/BOT System is Ready!

The Kitchen Order Ticket (KOT) and Bar Order Ticket (BOT) system has been successfully implemented in your Ravon POS system.

## 📍 Where to Find It

Look for **"KOT/BOT Orders"** in your sidebar navigation:
- **Admin Users**: Under the Reports section
- **Staff Users**: In the main navigation menu

## 🚀 Getting Started in 5 Steps

### Step 1: Classify Your Items (One-time Setup)
1. Go to **Item Management**
2. For each item, set the **Item Type**:
   - **Kitchen** - For food items (bread, pastries, meals, etc.)
   - **Bar** - For beverages (coffee, tea, juice, etc.)
   - **Both** - For items that could be either

### Step 2: Create Your First Order
1. Click **"KOT/BOT Orders"** in sidebar
2. Click **"New Order"** button (green button)
3. Fill in:
   - **Order Type**: Dine-In / Take-Away / Delivery
   - **Table Number**: (if dine-in)
   - **Special Notes**: Any general instructions
4. Click on items to add them to the order
5. Adjust quantities if needed
6. Add special instructions to specific items
7. Click **"Submit Orders"**
8. Order ticket will auto-print (if printer configured)

### Step 3: View Orders
- All orders appear in the **KOT/BOT Orders** list
- Filter by:
  - **Type**: All / KOT / BOT
  - **Status**: Pending / Preparing / Ready / Served / Completed
  - **Date Range**: Custom date filtering

### Step 4: Kitchen/Bar Display (Optional but Recommended)
1. Open a browser on kitchen/bar display device
2. Navigate to: **KOT/BOT Orders** → **Kitchen Display** (or Bar Display)
3. Leave it open - it auto-refreshes
4. Kitchen/bar staff can:
   - See all pending orders in real-time
   - Click "Start Preparing"
   - Update item status as they complete
   - Click "Mark as Ready" when done

### Step 5: Convert to Sale & Payment
1. When customer is ready to pay
2. Go to **KOT/BOT Orders** list
3. Find the order
4. Click **"Convert to Sale"** button (green icon)
5. Select payment method
6. Enter payment amount
7. Click **"Convert to Sale"**
8. Receipt generates automatically
9. Inventory updates automatically

## 🎯 Quick Tips

### For Cashiers/Waiters:
- ✅ Create orders as soon as customer places them
- ✅ Use table numbers for dine-in orders
- ✅ Add special instructions clearly (e.g., "No onions", "Extra spicy")
- ✅ Check order status before converting to sale

### For Kitchen Staff:
- ✅ Update order status promptly
- ✅ Mark individual items as ready when done
- ✅ Watch the timer - orders turn red after 15 minutes
- ✅ Check special instructions carefully

### For Bar Staff:
- ✅ Same as kitchen - update status as you prepare
- ✅ BOT orders are separate from KOT
- ✅ Use your dedicated Bar Display screen

## 🔍 Understanding Order Status

| Status | Color | Meaning | Who Updates |
|--------|-------|---------|-------------|
| **Pending** | 🟡 Yellow | Just created, not started | Auto |
| **Preparing** | 🔵 Blue | Being worked on | Kitchen/Bar |
| **Ready** | 🟢 Green | Ready to serve | Kitchen/Bar |
| **Served** | 🟣 Purple | Delivered to customer | Waiter |
| **Completed** | ⚫ Gray | Paid and closed | Auto (after sale) |
| **Cancelled** | 🔴 Red | Order cancelled | Admin/Manager |

## 📱 Mobile Usage

The system works on mobile devices too!
- Cashiers can create orders from tablets
- Kitchen/bar staff can use tablets to update status
- Responsive design adapts to all screen sizes

## 🖨️ Printing

### Thermal Printer Setup:
1. Connect 80mm thermal printer to computer
2. Configure as default printer in Windows
3. Orders auto-print when created
4. Can manually reprint from order details

### What Prints:
- Restaurant name and logo
- Order number (KOT/BOT######)
- Table number and order type
- All items with quantities
- Special instructions (highlighted)
- Timestamp and staff name

## 🔧 Troubleshooting

### "Items not showing in create order screen"
→ Make sure items are marked as **Active** and have **Item Type** set

### "Can't convert to sale"
→ Order must be in **Ready** or **Served** status first

### "Kitchen display not updating"
→ Refresh the page or check internet connection

### "Print not working"
→ Check printer is on and set as default printer

## 📊 Reports & Analytics

View order statistics:
- Total orders by type (KOT vs BOT)
- Average preparation time
- Orders by status
- Daily/weekly/monthly volumes
- Filter by date range

## 🎓 Training Your Team

### For New Staff:
1. Show them this quick start guide
2. Let them create a test order
3. Walk through the kitchen display
4. Practice converting to sale
5. Show how to print/reprint tickets

### Recommended Training Order:
1. **Day 1**: Create and view orders
2. **Day 2**: Kitchen/Bar display usage
3. **Day 3**: Converting to sales
4. **Day 4**: Full workflow practice
5. **Day 5**: Troubleshooting and tips

## 💡 Best Practices

1. **Create orders promptly** - Don't delay entering orders
2. **Use clear instructions** - Be specific in special notes
3. **Update status quickly** - Keep displays current
4. **Check before serving** - Verify all items marked ready
5. **Print backup tickets** - If kitchen loses printed ticket
6. **Use table numbers** - Essential for dine-in management
7. **Review end of day** - Check for any uncompleted orders

## 🎨 Customization Options

You can customize:
- Order number format
- Status colors
- Auto-refresh intervals
- Print template design
- Display screen layout

Contact your system administrator for customizations.

## 📞 Need Help?

1. Check the full documentation: `KOT_BOT_SYSTEM_README.md`
2. Review this quick start guide
3. Ask your system administrator
4. Check for system updates

## ✅ Checklist for First Day

- [ ] Classify all items with Item Type
- [ ] Create a test KOT order
- [ ] Create a test BOT order
- [ ] Test kitchen display
- [ ] Test bar display
- [ ] Test printing a ticket
- [ ] Convert test order to sale
- [ ] Train all staff members
- [ ] Set up dedicated display devices
- [ ] Configure thermal printer

## 🎉 You're Ready!

Your KOT/BOT system is fully operational and ready to streamline your restaurant operations!

**Happy ordering! 🍽️🥤**

---

*For detailed technical documentation, see KOT_BOT_SYSTEM_README.md*  
*For implementation details, see KOT_BOT_IMPLEMENTATION_SUMMARY.md*
