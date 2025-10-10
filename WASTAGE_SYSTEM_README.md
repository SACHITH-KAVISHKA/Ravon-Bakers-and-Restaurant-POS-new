# Wastage Management System

## Overview
The Wastage Management System allows supervisors to record and track wasted inventory items. When items are wasted, the system automatically reduces the inventory quantities and maintains a comprehensive record for auditing purposes.

## Features

### 1. Add Wastage Form (`/supervisor/add-wastage`)
- **Date & Time**: Auto-filled with current date and time, editable by supervisors
- **Dynamic Items Table**: 
  - Dropdown to select existing inventory items
  - Read-only field showing available stock for selected item
  - Input field for wasted quantity
  - Add/remove rows functionality for multiple items
- **Remarks**: Optional field for additional notes about the wastage
- **Validation**: 
  - Wasted quantity cannot exceed available stock
  - Clear error messages for validation failures
  - Prevents duplicate item selection

### 2. Wastage View Page (`/supervisor/wastage-view`)
- **Comprehensive Records**: View all wastage records created by the logged-in supervisor
- **Filtering Options**:
  - Filter by date range (from/to dates)
  - Search by item name
  - Clear filters functionality
- **Detailed Information**:
  - Date & Time of wastage
  - Number of items wasted
  - Total wasted quantity
  - Remarks
- **Modal Details**: Click "View Details" to see complete information including:
  - All wasted items with quantities
  - Previous stock levels
  - Remaining stock after wastage

### 3. Dashboard Integration
- **Statistics Card**: Shows total wastage records count
- **Quick Actions**: Direct buttons to add wastage and view records
- **Recent Wastages**: Latest 5 wastage records in dashboard

## Database Structure

### Tables Created:
1. **`wastages`**:
   - `id`: Primary key
   - `user_id`: Foreign key to users table (supervisor who recorded)
   - `date_time`: When the wastage occurred
   - `remarks`: Optional notes about the wastage
   - `created_at`, `updated_at`: Timestamps

2. **`wastage_items`**:
   - `id`: Primary key
   - `wastage_id`: Foreign key to wastages table
   - `item_id`: Foreign key to items table
   - `wasted_quantity`: Amount wasted
   - `previous_stock`: Stock level before wastage
   - `created_at`, `updated_at`: Timestamps

## Models and Relationships

### Wastage Model
- Belongs to User (supervisor)
- Has many WastageItems
- Includes computed attribute for total wasted quantity

### WastageItem Model
- Belongs to Wastage
- Belongs to Item
- Includes computed attribute for remaining stock

### Updated Existing Models
- **User**: Added relationship to Wastage
- **Item**: Added relationship to WastageItem

## Routes
All routes are protected by supervisor authentication middleware:

- `GET /supervisor/add-wastage` - Show add wastage form
- `POST /supervisor/store-wastage` - Process wastage submission
- `GET /supervisor/wastage-view` - View wastage records with filtering

## Controller Methods

### SupervisorController
- `addWastage()`: Show wastage form with available items
- `storeWastage()`: Validate and store wastage, update inventory
- `wastageView()`: Display wastage records with filtering options
- `dashboard()`: Updated to include wastage statistics

## Form Validation
- **Server-side**: Laravel validation for required fields, data types, and custom stock validation
- **Client-side**: JavaScript validation for user experience
- **Stock Validation**: Custom validation ensures wasted quantity doesn't exceed available stock

## Inventory Integration
When wastage is recorded:
1. System validates available stock for each item
2. Creates wastage and wastage_item records
3. Automatically decrements inventory quantities
4. Maintains audit trail with previous stock levels

## User Interface
- **Responsive Design**: Works on all device sizes
- **Bootstrap Components**: Modern, clean interface
- **Interactive Elements**: Dynamic row addition/removal
- **Real-time Validation**: Immediate feedback on stock availability
- **Filtering**: Advanced search and filter capabilities

## Security
- **Authentication**: Only authenticated supervisors can access
- **Authorization**: Supervisors can only view their own wastage records
- **Data Validation**: Server-side validation prevents invalid data
- **CSRF Protection**: Laravel CSRF tokens on all forms

## Usage Workflow
1. Supervisor logs in and navigates to Add Wastage
2. Selects date/time (defaults to current)
3. Adds items by selecting from dropdown
4. System shows available stock for selected item
5. Enters wasted quantity (validated against stock)
6. Adds optional remarks
7. Submits form - system updates inventory automatically
8. Can view all records in Wastage View page with filtering options

## Error Handling
- Clear validation messages for all error conditions
- Graceful handling of edge cases (no stock, invalid quantities)
- User-friendly error messages in forms
- Proper error logging for debugging

This system provides a complete solution for tracking inventory wastage while maintaining data integrity and providing comprehensive audit trails.