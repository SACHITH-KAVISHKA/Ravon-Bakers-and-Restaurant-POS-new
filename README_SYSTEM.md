# Ravon Restaurant Management System

## Overview
A comprehensive Laravel 11 Restaurant Management System with POS functionality, built with Bootstrap 5 and role-based access control.

## Features Implemented

### 🔐 Authentication & Authorization
- **Laravel Breeze** for authentication
- **Role-based Access Control (RBAC)** with two roles:
  - **Admin**: Full CRUD permissions on all modules
  - **Staff**: View-only access, can use POS system

### 🏠 Dashboard
- Real-time statistics: Total Items, Purchases, Stock Value, Today's Sales
- Low stock alerts
- Recent sales overview
- Quick action buttons

### 📦 Item Management
- CRUD operations for restaurant items
- Fields: ID, Item Name, Item Code, Category, Price, Description, Stock Quantity
- Categories: Bakery, Savory, Chicken, Sweet, Egg, Burgers, Drinks
- Inventory integration
- Low stock alerts

### 🛒 Purchase Management
- Manage supplier purchases
- Fields: Supplier Name, Purchase Date, Item, Quantity, Unit Price, Total Cost
- Auto-update inventory after purchases

### 📊 Inventory Management
- Track current stock levels
- Auto-update after POS sales
- Low stock alert system
- Real-time stock monitoring

### 👥 User Management (Admin Only)
- Create and manage user accounts
- Assign roles (Admin/Staff)
- User profile management

### 💰 POS (Point of Sale) System
- **Modern Interface** replicating the provided screenshots
- Product grid organized by categories
- Order management with quantity controls
- Multiple payment methods:
  - Cash
  - Card
  - Card & Cash
  - Credit
  - Complimentary
  - Online
- Real-time total calculations
- Receipt generation and printing

### 🧾 Receipt System
- Professional receipt layout
- Complete transaction details
- Print functionality
- Restaurant branding

## Technical Specifications

### Backend
- **Framework**: Laravel 12 (latest version)
- **Database**: SQLite (easily changeable to MySQL)
- **Authentication**: Laravel Breeze
- **Authorization**: Laravel Policies & Gates

### Frontend
- **CSS Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons
- **JavaScript**: Vanilla JS (no jQuery dependency)
- **Responsive Design**: Mobile-friendly sidebar and layout

### Database Schema
- `users` - User accounts with roles
- `items` - Restaurant menu items
- `purchases` - Supplier purchase records
- `inventories` - Stock tracking
- `sales` - POS transaction records
- `sale_items` - Individual items in each sale

### Security Features
- CSRF protection
- Input validation
- SQL injection prevention
- Role-based authorization
- Secure password hashing

## Pre-loaded Sample Data

### Default Users
- **Admin User**
  - Email: admin@revon.com
  - Password: password
  - Role: Admin (Full access)

- **Staff User**
  - Email: staff@revon.com
  - Password: password
  - Role: Staff (View-only + POS access)

### Sample Menu Items
**32 pre-loaded items** including:
-- Bacon Egg Pastry (LKR 170.00)
-- Butter Croissants (LKR 200.00)
-- Cheese Toast (LKR 130.00)
-- Chicken Roll (LKR 170.00)
-- Crispy Chicken Burger (LKR 460.00)
- And many more across all categories

## Installation & Setup

1. **Database Setup** (Already configured with SQLite)
2. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate:fresh --seed
   ```
3. **Start Development Server**:
   ```bash
   php artisan serve --port=8081
   ```
4. **Access Application**: http://localhost:8081

## Access URLs

- **Login**: http://localhost:8081/login
- **Dashboard**: http://localhost:8081/dashboard
- **POS System**: http://localhost:8081/pos
- **Item Management**: http://localhost:8081/items
- **Purchase Management**: http://localhost:8081/purchases
- **Inventory Management**: http://localhost:8081/inventory
- **User Management**: http://localhost:8081/users (Admin only)

## Key Features of POS System

### Interface Design
- **Ravon Bakers** branded header
- **Left Panel**: Categorized item grid with easy selection
- **Right Panel**: Order management, totals, and payment processing
- **Color-coded** categories for easy navigation
- **Responsive design** for different screen sizes

### Functionality
- **Click to Add**: Items are added to cart with single click
- **Quantity Control**: Increase/decrease item quantities
- **Remove Items**: Individual item removal from cart
- **Payment Methods**: Multiple payment options
- **Real-time Calculations**: Automatic total updates
- **Receipt Generation**: Professional receipt printing

### Categories in POS
- Bakery Items (Blue theme)
- Savory Items (Teal theme)
- Chicken Items (Green theme)
- Sweet Items (Purple theme)
- Egg Items (Orange theme)
- Burgers (Red theme)

## Role Permissions

### Admin Capabilities
- ✅ Full CRUD on Items
- ✅ Full CRUD on Purchases
- ✅ Full CRUD on Inventory
- ✅ Full CRUD on Users
- ✅ Access to POS System
- ✅ View all reports and analytics

### Staff Capabilities
- 👁️ View Items (Read-only)
- 👁️ View Purchases (Read-only)
- 👁️ View Inventory (Read-only)
- ❌ No User Management access
- ✅ Full access to POS System
- 👁️ View basic reports

## Future Enhancements

- **Reports Module**: Sales reports, inventory reports
- **Barcode Integration**: Item barcode scanning
- **Customer Management**: Customer records and loyalty
- **Multi-branch Support**: Multiple restaurant locations
- **Advanced Analytics**: Charts and graphs
- **SMS/Email Receipts**: Digital receipt delivery
- **Kitchen Display System**: Order management for kitchen

## Architecture Highlights

- **MVC Pattern**: Clean separation of concerns
- **Policy-based Authorization**: Scalable permission system
- **Database Transactions**: Data integrity for complex operations
- **Responsive UI**: Works on desktop, tablet, and mobile
- **RESTful Routes**: Standard Laravel resource routing
- **Component-based Views**: Reusable Blade components

## File Structure

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── ItemController.php
│   ├── PurchaseController.php
│   ├── InventoryController.php
│   ├── UserController.php
│   └── POSController.php
├── Models/
│   ├── User.php
│   ├── Item.php
│   ├── Purchase.php
│   ├── Inventory.php
│   ├── Sale.php
│   └── SaleItem.php
├── Policies/
│   ├── UserPolicy.php
│   ├── ItemPolicy.php
│   ├── PurchasePolicy.php
│   └── InventoryPolicy.php
└── Providers/
    └── AuthServiceProvider.php

resources/views/
├── layouts/
│   └── app.blade.php
├── dashboard.blade.php
├── items/
│   ├── index.blade.php
│   └── create.blade.php
└── pos/
    ├── index.blade.php
    └── receipt.blade.php

database/
├── migrations/
└── seeders/
    ├── AdminUserSeeder.php
    └── ItemSeeder.php
```

This system provides a complete restaurant management solution with modern UI, robust backend, and scalable architecture. The POS system replicates the interface shown in your screenshots while adding modern functionality and responsive design.