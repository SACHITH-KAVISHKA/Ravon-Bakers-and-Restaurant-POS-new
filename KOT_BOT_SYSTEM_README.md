# KOT/BOT (Kitchen Order Ticket / Bar Order Ticket) System

## Overview
The KOT/BOT system is a comprehensive order management solution for restaurant operations, allowing separate handling of kitchen and bar orders with full tracking and status management.

## Features

### 1. Order Creation
- **Separate Order Types**: Create KOT (Kitchen Order Ticket) or BOT (Bar Order Ticket) based on item type
- **Order Types**: Dine-In, Take-Away, or Delivery
- **Table Management**: Assign orders to specific tables
- **Special Instructions**: Add notes to individual items or entire orders
- **Real-time Pricing**: Automatic price calculation based on branch and item

### 2. Item Classification
Items are classified into three types:
- **Kitchen Items**: Food items that go to kitchen (creates KOT)
- **Bar Items**: Beverage items that go to bar (creates BOT)
- **Both**: Items that can go to either kitchen or bar (user chooses)

### 3. Order Workflow

#### Status Flow:
1. **Pending** → Order created, waiting to be prepared
2. **Preparing** → Kitchen/Bar has started working on the order
3. **Ready** → Order is ready to be served
4. **Served** → Order has been delivered to customer
5. **Completed** → Order converted to sale and payment received
6. **Cancelled** → Order was cancelled

### 4. Kitchen/Bar Display
- **Dedicated Display Screens**: Separate displays for kitchen and bar
- **Real-time Updates**: Automatically refreshes with new orders
- **Timer**: Shows how long each order has been waiting
- **Status Management**: Kitchen/Bar staff can update item and order status
- **Color-coded Status**: Visual indicators for quick identification
- **Order Priority**: Orders sorted by creation time

### 5. Order Management
- **View Orders**: List all KOT/BOT with filtering options
- **Search & Filter**: Filter by type, status, date range
- **Print Orders**: Thermal printer-friendly ticket printing
- **Convert to Sale**: Convert ready orders to sales with payment processing
- **Order Details**: View complete order information with items and status

### 6. Integration with POS
- Orders can be converted to sales
- Automatic inventory deduction
- Payment processing integration
- Receipt generation

## Database Structure

### Tables Created:

#### 1. `kots` Table
- `id`: Primary key
- `kot_no`: Unique KOT/BOT number (e.g., KOT2510310001, BOT2510310001)
- `type`: KOT or BOT
- `sale_id`: Link to sale when completed (nullable)
- `branch_id`: Branch where order was created
- `user_id`: User who created the order
- `user_name`: Name of the user
- `table_no`: Table number (nullable)
- `order_type`: Dine-In, Take-Away, or Delivery
- `status`: Current order status
- `notes`: Special instructions for the order
- `prepared_at`: Timestamp when marked as ready
- `served_at`: Timestamp when served
- `completed_at`: Timestamp when converted to sale
- `created_at` & `updated_at`: Timestamps

#### 2. `kot_items` Table
- `id`: Primary key
- `kot_id`: Foreign key to kots table
- `item_id`: Foreign key to items table
- `item_name`: Item name (denormalized)
- `quantity`: Quantity ordered
- `unit_price`: Price per unit
- `total_price`: Total price (quantity × unit_price)
- `status`: Item status (Pending, Preparing, Ready)
- `special_instructions`: Item-specific notes
- `created_at` & `updated_at`: Timestamps

#### 3. `items` Table (Modified)
- Added `item_type` field: Kitchen, Bar, or Both

## Routes

### Main Routes:
- `GET /kot` - List all KOT/BOT orders
- `GET /kot/create` - Create new order form
- `POST /kot/store` - Store new order
- `GET /kot/{kot}` - View order details
- `POST /kot/{kot}/status` - Update order status
- `POST /kot/item/{kotItem}/status` - Update item status
- `POST /kot/{kot}/convert-to-sale` - Convert to sale
- `GET /kot/{kot}/print` - Print order ticket
- `GET /kot/display/kitchen` - Kitchen display screen
- `GET /kot/pending` - Get pending orders (AJAX)

## Usage Guide

### For Cashiers/Waiters:

1. **Creating an Order**:
   - Navigate to KOT/BOT Orders → Create New Order
   - Select Order Type (Dine-In/Take-Away/Delivery)
   - Enter Table Number (if applicable)
   - Click on items to add them to the order
   - Kitchen items go to KOT, Bar items go to BOT
   - Add special instructions if needed
   - Click "Submit Orders" to create

2. **Managing Orders**:
   - View all orders in the KOT/BOT Orders list
   - Filter by type, status, or date
   - Click "View" to see order details
   - Click "Print" to reprint the order ticket
   - Click "Convert to Sale" when order is ready for payment

3. **Converting to Sale**:
   - Select payment method
   - Enter payment amount
   - System generates receipt and updates inventory
   - Order status changes to "Completed"

### For Kitchen/Bar Staff:

1. **Kitchen/Bar Display**:
   - Access from sidebar link or dedicated device
   - View all pending orders in real-time
   - Orders show:
     * Order number
     * Table number
     * Items and quantities
     * Special instructions
     * Time elapsed since order creation

2. **Processing Orders**:
   - Click "Start Preparing" when you begin work on an order
   - Update individual item status as you complete them
   - Click "Mark as Ready" when all items are prepared
   - System automatically updates status when all items are ready

3. **Display Features**:
   - Auto-refreshes every 30 seconds
   - Sound notification for new orders (optional)
   - Color-coded status indicators:
     * Yellow: Pending
     * Blue: Preparing
     * Green: Ready
   - Timer shows elapsed time (turns red after 15 minutes)

### For Administrators:

1. **Item Management**:
   - Set item type (Kitchen/Bar/Both) when creating/editing items
   - This determines which department receives the order

2. **Reports**:
   - View all KOT/BOT orders with filtering
   - Export order data
   - Track order preparation times
   - Monitor order volumes by type

3. **System Configuration**:
   - Set up dedicated displays for kitchen and bar
   - Configure thermal printers for order tickets
   - Manage item classifications

## Printing

### Thermal Printer Support:
- Optimized for 80mm thermal printers
- Includes:
  * Restaurant logo/header
  * Order number and type
  * Order type and table number
  * Date and time
  * Items with quantities
  * Special instructions
  * Total items count

### Print Triggers:
- Automatic print on order creation
- Manual reprint from order list
- Print from order details page

## Technical Details

### Models:
- `Kot`: Main order model
- `KotItem`: Order line items
- `Item`: Extended with item_type field
- `Sale`: Link to completed orders

### Controllers:
- `KotController`: Handles all KOT/BOT operations

### Views:
- `kot/index.blade.php`: Order list
- `kot/create.blade.php`: Order creation
- `kot/show.blade.php`: Order details
- `kot/print.blade.php`: Print template
- `kot/kitchen.blade.php`: Kitchen/Bar display

## Future Enhancements

Potential improvements:
1. SMS/Email notifications to customers
2. Order modification after creation
3. Split bills for shared tables
4. Chef/Bartender performance metrics
5. Order scheduling for future delivery
6. Integration with online ordering platforms
7. Multi-language support for kitchen tickets
8. Voice order input
9. Customer display system
10. Kitchen video calling for clarifications

## Troubleshooting

### Common Issues:

1. **Orders not appearing in kitchen display**:
   - Check internet connection
   - Refresh the display page
   - Verify order status is Pending/Preparing/Ready

2. **Print not working**:
   - Check printer connection
   - Verify browser print permissions
   - Use Chrome for best compatibility

3. **Cannot convert to sale**:
   - Ensure order status is Ready or Served
   - Check payment method is selected
   - Verify payment amount is correct

## System Requirements

- Laravel 10.x or higher
- PHP 8.1 or higher
- MySQL/MariaDB database
- Modern web browser (Chrome recommended)
- Thermal printer (80mm) for order tickets
- Dedicated display devices for kitchen/bar (optional but recommended)

## Security

- All routes protected by authentication middleware
- Role-based access control
- CSRF protection on all forms
- XSS protection on user inputs
- SQL injection prevention through Eloquent ORM

## Support

For issues or questions:
1. Check this documentation
2. Review the source code comments
3. Contact system administrator

---

**Version**: 1.0.0  
**Last Updated**: October 31, 2025  
**Developed for**: Ravon Bakers & Restaurant POS System
