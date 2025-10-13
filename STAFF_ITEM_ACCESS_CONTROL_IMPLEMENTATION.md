# Staff Item Access Control Implementation

## Overview
This document describes the implementation of staff-specific item access control in the Ravon Bakers and Restaurant POS system. Staff members can now only see and use items they have personally accepted from inventory requests in both the Item Management section and POS system.

## Features Implemented

### 1. Database Schema Changes
- **New Migration**: `add_received_by_to_inventory_request_items_table`
  - Added `received_by` field (foreign key to users table)
  - Added `received_at` timestamp field
  - Both fields are nullable to support existing data

### 2. Model Updates

#### InventoryRequestItem Model
- Added `received_by` and `received_at` to fillable fields
- Added `received_at` datetime cast
- Added `receivedBy()` relationship to User model

#### User Model
- Added `receivedInventoryRequestItems()` relationship
- This tracks which inventory items a staff member has accepted

### 3. New Controller: StaffController
Located at: `app/Http/Controllers/StaffController.php`

**Methods implemented:**
- `pendingInventoryRequests()` - Shows inventory requests with unaccepted items
- `showInventoryRequest()` - Shows details of a specific request
- `acceptInventoryItems()` - Allows staff to accept specific items
- `myAcceptedItems()` - Shows items the staff member has accepted
- `getAvailableItems()` - Helper method to get items based on user role

### 4. Controller Modifications

#### ItemController (`app/Http/Controllers/ItemController.php`)
- Modified `index()` method to filter items based on user role
- **Staff**: Only see items they have accepted
- **Admin/Supervisor**: See all items (no restrictions)

#### POSController (`app/Http/Controllers/POSController.php`)
- Modified `index()` method to filter items based on user role
- **Staff**: Only see accepted items in POS system
- **Admin/Supervisor**: See all items in POS system

### 5. Routes Added
Located in: `routes/web.php`

```php
// Staff routes for inventory management
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/pending-inventory-requests', [StaffController::class, 'pendingInventoryRequests'])->name('pending-inventory-requests');
    Route::get('/inventory-request/{inventoryRequest}', [StaffController::class, 'showInventoryRequest'])->name('show-inventory-request');
    Route::post('/accept-inventory-items/{inventoryRequest}', [StaffController::class, 'acceptInventoryItems'])->name('accept-inventory-items');
    Route::get('/my-accepted-items', [StaffController::class, 'myAcceptedItems'])->name('my-accepted-items');
});
```

### 6. View Files Created

#### `resources/views/staff/pending-inventory-requests.blade.php`
- Shows all inventory requests with pending items
- Allows staff to select and accept multiple items
- Interactive checkbox functionality with JavaScript
- Shows which items are already accepted by other staff

#### `resources/views/staff/my-accepted-items.blade.php`
- Displays all items the current staff member has accepted
- Shows acceptance date, department, and request details
- Pagination support

### 7. Navigation Updates
Modified: `resources/views/layouts/app.blade.php`

Added navigation items for staff users:
- "Pending Inventory" - Links to pending inventory requests
- "My Accepted Items" - Links to accepted items list

## User Workflow

### For Staff Members:

1. **Login**: Staff logs into the system
2. **View Pending Requests**: Navigate to "Pending Inventory" in sidebar
3. **Accept Items**: 
   - View inventory requests created by supervisors
   - Select specific items they want to accept
   - Submit acceptance (records their user ID and timestamp)
4. **Use Items**: 
   - Item Management: Only shows items they've accepted
   - POS System: Only shows items they've accepted for sales

### For Supervisors:
- No change in workflow
- Continue creating inventory requests as before
- Can see all items (no restrictions)

### For Admins:
- No change in workflow
- Can see all items (no restrictions)
- Full system access

## Technical Details

### Database Structure
```sql
-- New fields added to inventory_request_items table
ALTER TABLE inventory_request_items 
ADD received_by BIGINT UNSIGNED NULL,
ADD received_at TIMESTAMP NULL,
ADD FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL;
```

### Access Control Logic
```php
// In ItemController and POSController
if ($user && $user->isStaff()) {
    $acceptedItemIds = InventoryRequestItem::where('received_by', $user->id)
        ->pluck('item_id')->unique()->toArray();
    $items = Item::whereIn('id', $acceptedItemIds)->where('is_active', true);
} else {
    $items = Item::where('is_active', true); // Admin/Supervisor see all
}
```

## Security Considerations

1. **Authorization**: All staff routes are protected with `auth` middleware
2. **Data Integrity**: Uses database transactions for item acceptance
3. **Validation**: Validates that items exist and belong to the request
4. **Foreign Key Constraints**: Ensures data consistency

## Benefits

1. **Accountability**: Track which staff member accepted which items
2. **Inventory Control**: Staff can only sell items they've physically received
3. **Audit Trail**: Complete history of item acceptance with timestamps
4. **Flexibility**: Supervisors and admins maintain full access
5. **User Experience**: Clear interface for staff to manage their inventory

## Testing

The implementation has been tested with:
- Database migrations successfully applied
- Model relationships working correctly
- Server starts without errors
- Sample data created for testing

## Files Modified/Created

### Modified Files:
- `app/Http/Controllers/ItemController.php`
- `app/Http/Controllers/POSController.php`
- `app/Models/InventoryRequestItem.php`
- `app/Models/User.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`

### New Files:
- `database/migrations/2025_10_12_221643_add_received_by_to_inventory_request_items_table.php`
- `app/Http/Controllers/StaffController.php`
- `resources/views/staff/pending-inventory-requests.blade.php`
- `resources/views/staff/my-accepted-items.blade.php`

## Conclusion

This implementation successfully restricts staff member access to only items they have personally accepted from inventory requests, while maintaining full access for supervisors and administrators. The system provides a clear workflow for staff to accept items and tracks all acceptance activities for audit purposes.