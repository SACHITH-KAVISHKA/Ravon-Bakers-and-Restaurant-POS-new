<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    
    <title>Ravon Bakers - POS System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }
        
        .pos-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .pos-header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            color: white;
            padding: 10px 20px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 14px;
        }
        
        .header-center {
            flex: 1;
            text-align: center;
            font-size: 20px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .header-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .pos-content {
            display: flex;
            flex: 1;
            height: calc(100vh - 60px); /* Adjusted for single header */
        }
        
        /* Left Panel - Categories */
        .categories-panel {
            width: 250px;
            background: #fff;
            border-right: 2px solid #dee2e6;
            overflow-y: auto;
            padding: 0;
        }
        
        /* Middle Panel - Items */
        .items-panel {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            overflow-y: auto;
        }
        
        /* Right Panel - Cart & Payment */
        .payment-panel {
            width: 400px;
            background: #fff;
            border-left: 2px solid #dee2e6;
            display: flex;
            flex-direction: column;
            padding: 0;
        }
        
        
        /* Category List Styles */
        .category-list {
            padding: 0;
        }
        
        .category-item {
            background: #fff;
            border: none;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            width: 100%;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .category-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }
        
        .category-item.active {
            background: #007bff;
            color: white;
            border-left: 4px solid #0056b3;
        }
        
        .category-item i {
            margin-right: 10px;
            font-size: 16px;
            width: 20px;
        }
        
        /* Items Grid Styles */
        .items-header {
            background: white;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            border-bottom: 2px solid #e9ecef;
            border-radius: 8px 8px 0 0;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }
        
        .item-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        
        .item-name {
            font-size: 14px;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .item-stock {
            font-size: 11px;
            color: #6c757d;
            background: #e8f5e8;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 8px;
            display: inline-block;
            border: 1px solid #d4edda;
        }
        
        .item-stock.low-stock {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .item-stock.out-of-stock {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .item-price {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
        }
        
        /* Payment Panel Styles */
        .receipt-info {
            background: #e3f2fd;
            padding: 6px;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }
        
        .receipt-id {
            font-size: 13px;
            font-weight: bold;
            color: #1976d2;
        }
        
        .cart-section {
              flex: 1;
              overflow-y: auto;
              padding: 4px;
              height: calc(100vh - 650px);
              min-height: 150px;
              max-height: 250px;
        }
        
        .cart-header {
            background: #f8f9fa;
            padding: 4px 6px;
            margin: -4px -4px 6px -4px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 14px;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        
        .cart-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            font-size: 14px;
            color: #343a40;
        }
        
        .cart-item-price {
            font-size: 12px;
            color: #6c757d;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 2px;
            margin: 0 4px;
        }
        
        .qty-btn {
            background: #dc3545;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        .qty-btn.plus {
            background: #28a745;
        }
        
        .qty-input {
            width: 40px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
            padding: 4px;
        }
        
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        
        .cart-total-price {
            font-weight: bold;
            color: #28a745;
            font-size: 13px;
        }
        
        .totals-section {
            background: #f8f9fa;
            padding: 10px;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            flex-shrink: 0;
            position: sticky;
            bottom: 0;
            z-index: 1;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
            padding: 4px 0;
        }
        
        .total-row.grand-total {
            font-weight: bold;
            font-size: 14px;
            color: #28a745;
            border-top: 2px solid #dee2e6;
            padding-top: 6px;
            margin-top: 6px;
        }
        
        /* Number Pad Styles */
        .number-pad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
            margin-top: 6px;
            max-height: 240px;
        }
        
        .number-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 45px;
            position: relative;
            outline: none;
        }
        
        .number-btn:hover {
            background: #0056b3;
            transform: scale(1.02);
        }
        
        .number-btn:active,
        .number-btn.pressed {
            background: #004085;
            transform: scale(0.98);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .number-btn.clear {
            background: #dc3545;
        }
        
        .number-btn.clear:hover {
            background: #c82333;
        }
        
        .number-btn.clear:active,
        .number-btn.clear.pressed {
            background: #a71e2a;
        }
        
        /* Quick Amount Buttons */
        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 3px;
            margin-top: 6px;
        }
        
        .quick-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 6px 4px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 30px;
        }
        
        .quick-btn:hover {
            background: #218838;
            transform: scale(1.02);
        }
        
        .quick-btn:active,
        .quick-btn.pressed {
            background: #1e7e34;
            transform: scale(0.98);
        }
        
        .quick-btn.exact-btn {
            background: #ffc107;
            color: #212529;
        }
        
        .quick-btn.exact-btn:hover {
            background: #e0a800;
        }
        
        .quick-btn.exact-btn:active,
        .quick-btn.exact-btn.pressed {
            background: #d39e00;
        }
        
        .payment-section {
            padding: 8px;
            flex-shrink: 0;
            flex-direction: column;
            max-height: 260px;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }
        
        .payment-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 8px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            min-height: 50px;
        }
        
        .payment-btn.active {
            background: #28a745;
            transform: translateY(-2px);
        }
        
        .payment-btn:hover {
            transform: translateY(-2px);
        }
        
        .cash-input-section {
            background: #fff3cd;
            padding: 3px;
            border-radius: 4px;
            margin-bottom: 6px;
            border: 1px solid #ffeaa7;
            display: none;
            flex: 1;
            overflow-y: auto;
        }
        
        .cash-input-section.show {
            display: block;
        }
        
        .cash-input-group {
            margin-bottom: 4px;
        }
        
        .cash-input-label {
            font-size: 11px;
            font-weight: 600;
            color: #856404;
            margin-bottom: 4px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .cash-input-label small {
            color: #6c757d;
            font-weight: normal;
            font-style: italic;
        }
        
        .input-mode-toggle {
            background: #007bff;
            color: white;
            border: none;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 8px;
        }
        
        .input-mode-toggle:hover {
            background: #0056b3;
        }
        
        .input-mode-toggle.touch-only {
            background: #6c757d;
        }
        
        .input-mode-toggle.touch-only:hover {
            background: #545b62;
        }
        
        .cash-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #d4ac0d;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            background: #fff;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .cash-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            background: #f8f9fa;
        }
        
        .cash-input:hover {
            border-color: #b8860b;
        }
        
        .balance-display {
            background: #d1ecf1;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            color: #0c5460;
            font-size: 14px;
            margin: 10px 0;
            border: 2px solid #bee5eb;
        }
        
        .checkout-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
            margin-top: 8px;
            position: sticky;
            bottom: 0;
            z-index: 2;
        }
        
        .checkout-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            transform: translateY(-1px);
        }
        
        .checkout-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .empty-cart {
            text-align: center;
            color: #6c757d;
            padding: 15px 8px;
        }
        
        .empty-cart i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #dee2e6;
        }
        
        .search-box {
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .search-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
        }

        /* Receipt Modal Styles */
        .receipt {
            font-family: 'Courier New', monospace;
            max-width: 300px;
            margin: 0 auto;
            background: white;
            padding: 20px;
        }
        
        .receipt .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .receipt .restaurant-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt .receipt-info {
            font-size: 12px;
            margin-bottom: 15px;
        }
        
        .receipt .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .receipt .items {
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .receipt .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .receipt .item-details {
            flex: 1;
        }
        
        .receipt .item-name {
            font-weight: bold;
        }
        
        .receipt .item-qty-price {
            color: #666;
            font-size: 10px;
        }
        
        .receipt .totals {
            font-size: 12px;
        }
        
        .receipt .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .receipt .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .receipt .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            border-top: 1px dashed #333;
            padding-top: 10px;
        }

        .receipt-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
        }

        .print-btn, .new-order-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .new-order-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .print-btn:hover, .new-order-btn:hover {
            transform: translateY(-2px);
            filter: brightness(110%);
        }
    </style>
</head>
<body>
    <!-- Header with Navigation -->
    <div class="pos-header">
        <div class="header-left">
            <button class="header-btn" onclick="goToDashboard()">
                <i class="bi bi-house"></i> Dashboard
            </button>
            <span>|</span>
            <span><i class="bi bi-person"></i> {{ Auth::user()->name }}</span>
            <span>|</span>
            <span id="current-time">{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="header-center">
            <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Logo" style="width: 32px; height: 32px; margin-right: 10px; border-radius: 50%; vertical-align: middle;">
            RAVON BAKERS - POS SYSTEM
        </div>
        <div class="header-right">
            <button class="header-btn" onclick="clearAllOrders()" style="margin-right: 10px; background: #dc3545;">
                <i class="bi bi-trash"></i> Clear
            </button>
            <button class="header-btn" onclick="toggleFullscreen()" id="fullscreen-btn">
                <i class="bi bi-fullscreen"></i> Fullscreen
            </button>
        </div>
    </div>

    <div class="pos-container">
        <div class="pos-content">
            <!-- Left Panel - Categories -->
            <div class="categories-panel">
                <div class="search-box">
                    <input type="text" class="search-input" id="item-search" placeholder="Search items...">
                </div>
                <div class="category-list">
                    <button type="button" class="category-item active" onclick="showAllCategories(this)">
                        <i class="bi bi-grid-3x3-gap"></i> All Items
                    </button>
                    @php
                        $categoryIcons = [
                            'Bakery' => 'bi-cookie',
                            'Savory' => 'bi-egg-fried',
                            'Beverages' => 'bi-cup-straw',
                            'Desserts' => 'bi-cake2',
                            'Snacks' => 'bi-bag'
                        ];
                    @endphp
                    @foreach($items as $category => $categoryItems)
                        <button type="button" class="category-item" onclick="showCategory('{{ $category }}', this)">
                            <i class="{{ $categoryIcons[$category] ?? 'bi-tag' }}"></i> {{ $category }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Middle Panel - Items -->
            <div class="items-panel">
                <div class="items-header">
                    <h5 class="mb-0" id="category-title">
                        <i class="bi bi-grid-3x3-gap me-2"></i>All Items
                    </h5>
                </div>
                
                <div class="items-grid" id="items-container">
                    @foreach($items as $category => $categoryItems)
                        @foreach($categoryItems as $item)
                            @php
                                $currentStock = $item->getCurrentStock();
                                $stockClass = '';
                                if ($currentStock == 0) {
                                    $stockClass = 'out-of-stock';
                                } elseif ($currentStock <= 5) {
                                    $stockClass = 'low-stock';
                                }
                            @endphp
                            <div class="item-card" 
                                 data-category="{{ $category }}" 
                                 data-item-id="{{ $item->id }}" 
                                 data-item-name="{{ $item->item_name }}" 
                                 data-item-price="{{ $item->price }}" 
                                 data-item-stock="{{ $item->getCurrentStock() }}"
                                 onclick="addToCartFromCard(this)">
                                <div class="item-name">{{ $item->item_name }}</div>
                                <div class="item-stock {{ $stockClass }}">Available: {{ $item->getCurrentStock() }}</div>
                                <div class="item-price">Rs. {{ number_format($item->price, 2) }}</div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <!-- Right Panel - Cart & Payment -->
            <div class="payment-panel">
                <!-- Receipt Info -->
                <div class="receipt-info">
                    <div class="receipt-id">RECEIPT: <span id="receipt-no">{{ 'RCP' . now()->format('ymd') . str_pad(\App\Models\Sale::whereDate('created_at', now()->toDateString())->count() + 1, 4, '0', STR_PAD_LEFT) }}</span></div>
                </div>

                <!-- Cart Section -->
                <div class="cart-section">
                    <div class="cart-header">
                        <i class="bi bi-cart3"></i> Order Items
                    </div>
                    <div id="cart-items">
                        <div class="empty-cart">
                            <i class="bi bi-cart-x"></i>
                            <div>Cart is empty</div>
                            <small>Select items to add to cart</small>
                        </div>
                    </div>
                </div>

                <!-- Totals Section -->
                <div class="totals-section">
                    <!-- <div class="total-row">
                        <span>Sub Total</span>
                        <span id="subtotal">Rs. 0.00</span>
                    </div> -->
                    <div class="total-row grand-total">
                        <span>TOTAL</span>
                        <span id="total">Rs. 0.00</span>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="payment-section">
                    <div class="payment-methods">
                        <button type="button" class="payment-btn active" data-method="CASH" onclick="selectPaymentMethod('CASH', this)">
                            <i class="bi bi-cash"></i> CASH
                        </button>
                        <button type="button" class="payment-btn" data-method="CARD" onclick="selectPaymentMethod('CARD', this)">
                            <i class="bi bi-credit-card"></i> CARD
                        </button>
                        <button type="button" class="payment-btn" data-method="CARD & CASH" onclick="selectPaymentMethod('CARD & CASH', this)">
                            <i class="bi bi-credit-card-2-front"></i> CARD & CASH
                        </button>
                        <button type="button" class="payment-btn" data-method="CREDIT" onclick="selectPaymentMethod('CREDIT', this)">
                            <i class="bi bi-journal-text"></i> CREDIT
                        </button>
                    </div>

                    <!-- Cash Input Section -->
                    <div class="cash-input-section show" id="cash-input-section">
                        <div class="cash-input-group">
                            <div class="cash-input-label">
                                Customer Payment 
                            
                                <button type="button" class="input-mode-toggle" id="input-mode-toggle" 
                                        onclick="toggleInputMode()" title="Toggle between touch-only and keyboard input">
                                    <i class="bi bi-keyboard"></i>
                                </button>
                            </div>
                            <input type="number" class="cash-input" id="customer-payment" placeholder="0.00" 
                                   step="0.01" min="0" 
                                   oninput="handleKeyboardInput()" 
                                   onkeydown="handleKeyboardKeys(event)"
                                   onfocus="this.select()"
                                   title="Enter payment amount using keyboard or touch pad below">
                            
                            <!-- Number Pad -->
                            <div class="number-pad">
                                <button type="button" class="number-btn" onclick="addToPayment('1')">1</button>
                                <button type="button" class="number-btn" onclick="addToPayment('2')">2</button>
                                <button type="button" class="number-btn" onclick="addToPayment('3')">3</button>
                                <button type="button" class="number-btn" onclick="addToPayment('4')">4</button>
                                <button type="button" class="number-btn" onclick="addToPayment('5')">5</button>
                                <button type="button" class="number-btn" onclick="addToPayment('6')">6</button>
                                <button type="button" class="number-btn" onclick="addToPayment('7')">7</button>
                                <button type="button" class="number-btn" onclick="addToPayment('8')">8</button>
                                <button type="button" class="number-btn" onclick="addToPayment('9')">9</button>
                                <button type="button" class="number-btn" onclick="addToPayment('0')">0</button>
                                <button type="button" class="number-btn" onclick="addToPayment('.')">.</button>
                                <button type="button" class="number-btn clear" onclick="clearPayment()">CLR</button>
                            </div>
<!--                          -->
                        </div>
                        <div class="balance-display" id="balance-display">
                            Balance: Rs. 0.00
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <button type="button" class="checkout-btn" id="checkout-btn" onclick="processCheckout()" disabled>
                        <i class="bi bi-credit-card"></i> Process Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="receiptContent">
                    <!-- Receipt content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Print Template -->
    <template id="receiptTemplate">
        <div class="receipt">
            <div class="header">
                <div style="text-align: center; margin-bottom: 10px;">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Logo" style="width: 40px; height: 40px; border-radius: 50%; margin-bottom: 8px;">
                </div>
                <div class="restaurant-name">RAVON BAKERS</div>
                <div style="font-size: 12px;">Restaurant & Bakery</div>
                <div style="font-size: 10px; margin-top: 5px;">
                    <div>Address: 282/A 2, Kaduwela</div>
                    <div>Phone: 076 200 6007</div>
                </div>
            </div>
            
            <div class="receipt-info">
                <div>
                    <span>RECEIPT NO:</span>
                    <span id="receipt-no-display"></span>
                </div>
                <div>
                    <span>USER:</span>
                    <span id="user-name-display"></span>
                </div>
                <div>
                    <span>DATE:</span>
                    <span id="date-display"></span>
                </div>
                <div>
                    <span>TIME:</span>
                    <span id="time-display"></span>
                </div>
            </div>
            
            <div class="items" id="receipt-items">
                <!-- Items will be inserted here -->
            </div>
            
            <div class="totals">
                <div class="total-row">
                    <span>Sub Total:</span>
                    <span id="receipt-subtotal"></span>
                </div>
                
                <div class="total-row grand-total">
                    <span>TOTAL:</span>
                    <span id="receipt-total"></span>
                </div>
            </div>
            
            <div class="receipt-info" style="margin-top: 15px;">
                <div>
                    <span>Payment Method:</span>
                    <span id="payment-method-display"></span>
                </div>
                
                <div id="cash-payment-details" style="display: none;">
                    <div>
                        <span>Amount Paid:</span>
                        <span id="amount-paid-display"></span>
                    </div>
                    <div>
                        <span>Balance:</span>
                        <span id="balance-display-receipt"></span>
                    </div>
                </div>
                

            </div>
            
            <div class="footer">
                <div>Thank you for visiting</div>
                <div><strong>RAVON RESTAURANT</strong></div>
                <div>Come again!</div>
                <div style="margin-top: 10px; font-size: 8px; color: #666;">
                    <div>System by SKM Labs</div>
                </div>
            </div>
        </div>
        
        <div class="receipt-actions">
            <button type="button" class="print-btn" onclick="downloadReceiptPDF()">
                <i class="bi bi-download"></i> Download PDF
            </button>
            <button type="button" class="new-order-btn" onclick="startNewOrder()">
                <i class="bi bi-plus-circle"></i> New Order
            </button>
        </div>
    </template>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 48px;"></i>
                    <p class="mt-3" id="errorModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 48px;"></i>
                    <p class="mt-3" id="successModalMessage">Payment Successful!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let cart = []; // Initialize empty cart
        let selectedPaymentMethod = 'CASH';
        let customerPayment = 0;

        // Test function to debug totals (can be removed later)
        function testTotalCalculation() {
            console.log('Testing total calculation...');
            console.log('Cart:', cart);
            const total = getTotalAmount();
            console.log('Calculated total:', total);
            updateTotals();
        }

        // Add item to cart from card click
        function addToCartFromCard(card) {
            const itemId = parseInt(card.dataset.itemId);
            const itemName = card.dataset.itemName;
            const price = parseFloat(card.dataset.itemPrice);
            const availableStock = parseInt(card.dataset.itemStock);
            
            console.log('Adding item from card:', {
                itemId,
                itemName,
                price,
                availableStock
            });
            
            if (isNaN(price) || price <= 0) {
                console.error('Invalid price:', card.dataset.itemPrice);
                showError('Invalid item price');
                return;
            }
            
            addToCart(itemId, itemName, price, availableStock);
        }

        // Add item to cart
        function addToCart(itemId, itemName, price, availableStock) {
            const existingItem = cart.find(item => item.id === itemId);
            const itemPrice = parseFloat(price) || 0;
            
            if (existingItem) {
                if (existingItem.quantity < availableStock) {
                    existingItem.quantity += 1;
                } else {
                    showError(`Only ${availableStock} items available in stock`);
                    return;
                }
            } else {
                cart.push({
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    quantity: 1,
                    availableStock: availableStock
                });
            }
            
            console.log('Item added to cart:', {
                id: itemId,
                name: itemName,
                price: itemPrice,
                quantity: existingItem ? existingItem.quantity : 1
            });
            
            updateCartDisplay();
        }

        // Remove item from cart
        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateCartDisplay();
        }

        // Update item quantity
        function updateQuantity(itemId, quantity) {
            const item = cart.find(item => item.id === itemId);
            if (item) {
                const newQuantity = Math.max(1, parseInt(quantity) || 1);
                if (newQuantity > item.availableStock) {
                    showError(`Only ${item.availableStock} items available in stock`);
                    return;
                }
                item.quantity = newQuantity;
                console.log('Quantity updated:', { itemId, newQuantity, price: item.price });
                updateCartDisplay();
            }
        }

        // Update cart display
        function updateCartDisplay() {
            const cartContainer = document.getElementById('cart-items');
            const checkoutBtn = document.getElementById('checkout-btn');
            
            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="empty-cart">
                        <i class="bi bi-cart-x"></i>
                        <div>Cart is empty</div>
                        <small>Select items to add to cart</small>
                    </div>`;
                checkoutBtn.disabled = true;
            } else {
                let html = '';
                cart.forEach(item => {
                    const itemPrice = parseFloat(item.price) || 0;
                    const itemQuantity = parseInt(item.quantity) || 0;
                    const totalPrice = itemPrice * itemQuantity;
                    
                    html += `
                        <div class="cart-item">
                            <div class="cart-item-details">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-price">Rs. ${itemPrice.toFixed(2)} each | Stock: ${item.availableStock || 'N/A'}</div>
                            </div>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="updateQuantity(${item.id}, ${itemQuantity - 1})">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="qty-input" value="${itemQuantity}" 
                                       onchange="updateQuantity(${item.id}, this.value)" min="1" max="${item.availableStock || 999}">
                                <button type="button" class="qty-btn plus" onclick="updateQuantity(${item.id}, ${itemQuantity + 1})">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <div class="cart-total-price">Rs. ${totalPrice.toFixed(2)}</div>
                            <button type="button" class="remove-btn" onclick="removeFromCart(${item.id})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>`;
                });
                cartContainer.innerHTML = html;
                checkoutBtn.disabled = false;
            }
            
            updateTotals();
        }

        // Update totals
        function updateTotals() {
            let subtotal = 0;
            
            // Calculate subtotal from cart items
            if (cart && cart.length > 0) {
                subtotal = cart.reduce((sum, item) => {
                    const itemPrice = parseFloat(item.price) || 0;
                    const itemQuantity = parseInt(item.quantity) || 0;
                    return sum + (itemPrice * itemQuantity);
                }, 0);
            }
            
            const total = subtotal; // No discount or tax
            
            // Update subtotal element if it exists
            const subtotalElement = document.getElementById('subtotal');
            if (subtotalElement) {
                subtotalElement.textContent = `Rs. ${subtotal.toFixed(2)}`;
            }
            
            // Update total element
            const totalElement = document.getElementById('total');
            if (totalElement) {
                totalElement.textContent = `Rs. ${total.toFixed(2)}`;
            }
            
            // Update balance if cash payment
            if (selectedPaymentMethod === 'CASH' || selectedPaymentMethod === 'CARD & CASH') {
                calculateBalance();
            }
            
            // Debug logging
            console.log('Cart items:', cart);
            console.log('Calculated subtotal:', subtotal);
            console.log('Calculated total:', total);
        }

        // Show category items
        function showCategory(category, button) {
            // Hide all items first
            const allItems = document.querySelectorAll('.item-card');
            allItems.forEach(item => {
                if (item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Update active category
            document.querySelectorAll('.category-item').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
            
        
            
            document.getElementById('category-title').innerHTML = 
                `<i class="${categoryIcons[category] || 'bi-tag'} me-2"></i>${category}`;
        }

        // Show all categories
        function showAllCategories(button) {
            // Show all items
            const allItems = document.querySelectorAll('.item-card');
            allItems.forEach(item => {
                item.style.display = 'block';
            });
            
            // Update active category
            document.querySelectorAll('.category-item').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
            
            // Update header
            document.getElementById('category-title').innerHTML = 
                '<i class="bi bi-grid-3x3-gap me-2"></i>All Items';
        }

        // Select payment method
        function selectPaymentMethod(method, button) {
            selectedPaymentMethod = method;
            
            // Update active payment button
            document.querySelectorAll('.payment-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Show/hide cash input section
            const cashInputSection = document.getElementById('cash-input-section');
            if (method === 'CASH' || method === 'CARD & CASH') {
                cashInputSection.classList.add('show');
                calculateBalance();
            } else {
                cashInputSection.classList.remove('show');
                // For card payments, set balance to 0
                const balanceDisplay = document.getElementById('balance-display');
                balanceDisplay.innerHTML = 'Balance: Rs. 0.00';
                balanceDisplay.style.background = '#d1ecf1';
                balanceDisplay.style.color = '#0c5460';
            }
        }

        // Input mode management
        let keyboardInputEnabled = true;
        
        function toggleInputMode() {
            keyboardInputEnabled = !keyboardInputEnabled;
            const toggle = document.getElementById('input-mode-toggle');
            const input = document.getElementById('customer-payment');
            
            if (keyboardInputEnabled) {
                toggle.innerHTML = '<i class="bi bi-keyboard"></i>';
                toggle.classList.remove('touch-only');
                toggle.title = 'Switch to touch-only mode';
                input.removeAttribute('readonly');
                input.title = 'Enter payment amount using keyboard or touch pad below';
            } else {
                toggle.innerHTML = '<i class="bi bi-hand-index"></i>';
                toggle.classList.add('touch-only');
                toggle.title = 'Switch to keyboard input mode';
                input.setAttribute('readonly', true);
                input.title = 'Use touch pad below to enter payment amount';
            }
        }

        // Number pad functions
        function addToPayment(digit) {
            const input = document.getElementById('customer-payment');
            let currentValue = input.value || '0';
            
            // Highlight the pressed button
            highlightNumberButton(digit);
            
            if (digit === '.') {
                if (!currentValue.includes('.')) {
                    input.value = currentValue + '.';
                }
            } else {
                if (currentValue === '0' || currentValue === '0.00') {
                    input.value = digit;
                } else {
                    input.value = currentValue + digit;
                }
            }
            
            // Trigger input event to ensure consistency
            input.dispatchEvent(new Event('input'));
            calculateBalance();
        }

        // Handle keyboard input
        function handleKeyboardInput() {
            if (!keyboardInputEnabled) return;
            
            const input = document.getElementById('customer-payment');
            let value = input.value;
            
            // Validate input
            if (value === '' || value === null) {
                input.value = '0';
                value = '0';
            }
            
            // Ensure only valid numbers
            if (isNaN(parseFloat(value))) {
                input.value = '0';
            }
            
            calculateBalance();
        }
        
        // Handle special keyboard keys
        function handleKeyboardKeys(event) {
            if (!keyboardInputEnabled) {
                event.preventDefault();
                return;
            }
            
            // Allow backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(event.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (event.keyCode === 65 && event.ctrlKey === true) ||
                (event.keyCode === 67 && event.ctrlKey === true) ||
                (event.keyCode === 86 && event.ctrlKey === true) ||
                (event.keyCode === 88 && event.ctrlKey === true) ||
                // Allow home, end, left, right
                (event.keyCode >= 35 && event.keyCode <= 39)) {
                return;
            }
            
            // Ensure that it is a number or decimal point and stop the keypress
            if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && 
                (event.keyCode < 96 || event.keyCode > 105) && 
                event.keyCode !== 110 && event.keyCode !== 190) {
                event.preventDefault();
            }
            
            // Prevent multiple decimal points
            if ((event.keyCode === 110 || event.keyCode === 190) && 
                event.target.value.indexOf('.') !== -1) {
                event.preventDefault();
            }
        }
        
        // Highlight number button when pressed
        function highlightNumberButton(digit) {
            const buttons = document.querySelectorAll('.number-btn');
            buttons.forEach(button => {
                if (button.textContent === digit) {
                    button.classList.add('pressed');
                    setTimeout(() => {
                        button.classList.remove('pressed');
                    }, 150);
                }
            });
        }

        function clearPayment() {
            const input = document.getElementById('customer-payment');
            input.value = '0';
            input.focus();
            
            // Highlight clear button
            const clearBtn = document.querySelector('.number-btn.clear');
            if (clearBtn) {
                clearBtn.classList.add('pressed');
                setTimeout(() => {
                    clearBtn.classList.remove('pressed');
                }, 150);
            }
            
            calculateBalance();
        }
        
        // Quick amount functions
        function setQuickAmount(amount) {
            const input = document.getElementById('customer-payment');
            input.value = amount.toString();
            
            // Highlight the pressed button
            const buttons = document.querySelectorAll('.quick-btn');
            buttons.forEach(button => {
                if (button.textContent === amount.toString()) {
                    button.classList.add('pressed');
                    setTimeout(() => {
                        button.classList.remove('pressed');
                    }, 150);
                }
            });
            
            calculateBalance();
        }
        
        function setExactAmount() {
            const total = getTotalAmount();
            const input = document.getElementById('customer-payment');
            input.value = total.toFixed(2);
            
            // Highlight exact button
            const exactBtn = document.querySelector('.quick-btn.exact-btn');
            if (exactBtn) {
                exactBtn.classList.add('pressed');
                setTimeout(() => {
                    exactBtn.classList.remove('pressed');
                }, 150);
            }
            
            calculateBalance();
        }

        // Calculate balance for cash payments
        function calculateBalance() {
            let total = 0;
            if (cart && cart.length > 0) {
                total = cart.reduce((sum, item) => {
                    const itemPrice = parseFloat(item.price) || 0;
                    const itemQuantity = parseInt(item.quantity) || 0;
                    return sum + (itemPrice * itemQuantity);
                }, 0);
            }
            
            const customerPaymentInput = document.getElementById('customer-payment');
            const balanceDisplay = document.getElementById('balance-display');
            
            customerPayment = parseFloat(customerPaymentInput.value) || 0;
            const balance = customerPayment - total;
            
            console.log('Balance calculation:', { total, customerPayment, balance });
            
            if (balance >= 0) {
                balanceDisplay.innerHTML = `<i class="bi bi-check-circle me-2"></i>Balance: Rs. ${balance.toFixed(2)}`;
                balanceDisplay.style.background = '#d1ecf1';
                balanceDisplay.style.color = '#0c5460';
                document.getElementById('checkout-btn').disabled = cart.length === 0;
            } else if (customerPayment > 0) {
                balanceDisplay.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>Insufficient: Rs. ${Math.abs(balance).toFixed(2)}`;
                balanceDisplay.style.background = '#f8d7da';
                balanceDisplay.style.color = '#721c24';
                document.getElementById('checkout-btn').disabled = true;
            } else {
                balanceDisplay.innerHTML = 'Balance: Rs. 0.00';
                balanceDisplay.style.background = '#d1ecf1';
                balanceDisplay.style.color = '#0c5460';
                document.getElementById('checkout-btn').disabled = cart.length === 0;
            }
        }

        // Fullscreen functionality
        function toggleFullscreen() {
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
            } else {
                document.exitFullscreen();
                fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen"></i> Fullscreen';
            }
        }
        
        // Listen for fullscreen changes
        document.addEventListener('fullscreenchange', function() {
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            if (document.fullscreenElement) {
                fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
            } else {
                fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen"></i> Fullscreen';
            }
        });

        // Navigation function
        function goToDashboard() {
            window.location.href = '{{ route("dashboard") }}';
        }

        // Update time display
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            document.getElementById('current-time').textContent = timeString;
        }

        // Clear all items
        function clearAll() {
            if (confirm('Are you sure you want to clear all items from the cart?')) {
                cart = [];
                updateCartDisplay();
                document.getElementById('customer-payment').value = '0';
                calculateBalance();
            }
        }

        // Show error modal
        function showError(message) {
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorModalMessage').textContent = message;
            errorModal.show();
        }

        // Show success modal
        function showSuccess(message) {
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            document.getElementById('successModalMessage').textContent = message;
            successModal.show();
            setTimeout(() => {
                successModal.hide();
            }, 2000);
        }

        // Process checkout
        function processCheckout() {
            if (cart.length === 0) {
                showError('Please add items to cart before checkout');
                return;
            }

            // Only validate cash payment for cash and card & cash methods
            if ((selectedPaymentMethod === 'CASH' || selectedPaymentMethod === 'CARD & CASH')) {
                if (customerPayment < getTotalAmount()) {
                    showError('Insufficient payment amount');
                    return;
                }
            }

            const orderData = {
                items: cart,
                payment_method: selectedPaymentMethod,
                customer_payment: (selectedPaymentMethod === 'CASH' || selectedPaymentMethod === 'CARD & CASH') ? customerPayment : getTotalAmount()
            };

            // Disable checkout button
            const checkoutBtn = document.getElementById('checkout-btn');
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

            fetch('{{ route("pos.process-sale") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal briefly
                    showSuccess('Payment Successful!');
                    
                    // Store cart data before clearing for PDF generation
                    window.lastSaleData = {
                        cart: [...cart],
                        paymentMethod: selectedPaymentMethod,
                        customerPayment: customerPayment,
                        receiptData: data
                    };
                    
                    // Populate and show receipt modal
                    populateReceipt(data);
                    
                    // Clear the cart
                    cart = [];
                    document.getElementById('customer-payment').value = '0';
                    
                    // Show receipt modal
                    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
                    receiptModal.show();
                } else {
                    showError(data.message || 'Error processing payment');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error processing payment. Please try again.');
            })
            .finally(() => {
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = '<i class="bi bi-credit-card"></i> Process Payment';
            });
        }

        // Get total amount
        function getTotalAmount() {
            if (!cart || cart.length === 0) {
                return 0;
            }
            return cart.reduce((sum, item) => {
                const itemPrice = parseFloat(item.price) || 0;
                const itemQuantity = parseInt(item.quantity) || 0;
                return sum + (itemPrice * itemQuantity);
            }, 0);
        }

        // Clear all orders function
        function clearAllOrders() {
            cart = [];
            updateCartDisplay();
            document.getElementById('customer-payment').value = '0';
            calculateBalance();
        }

        // Search functionality
        document.getElementById('item-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.item-card');
            
            items.forEach(item => {
                const itemName = item.dataset.itemName.toLowerCase();
                
                if (itemName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Populate receipt with sale data
        function populateReceipt(data) {
            // Get template content
            const template = document.getElementById('receiptTemplate');
            const receiptContent = document.getElementById('receiptContent');
            receiptContent.innerHTML = template.innerHTML;
            
            // Populate receipt info
            document.getElementById('receipt-no-display').textContent = data.receipt_no;
            document.getElementById('user-name-display').textContent = data.user_name;
            document.getElementById('date-display').textContent = new Date().toLocaleDateString('en-GB');
            document.getElementById('time-display').textContent = new Date().toLocaleTimeString('en-GB');
            
            // Populate items
            const itemsContainer = document.getElementById('receipt-items');
            let itemsHtml = '';
            const cartData = window.lastSaleData ? window.lastSaleData.cart : cart;
            cartData.forEach(item => {
                itemsHtml += `
                    <div class="item">
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-qty-price">${item.quantity} x Rs. ${item.price.toFixed(2)}</div>
                        </div>
                        <div>Rs. ${(item.price * item.quantity).toFixed(2)}</div>
                    </div>`;
            });
            itemsContainer.innerHTML = itemsHtml;
            
            // Helper function to format numbers
            const formatNumber = (num) => {
                if (typeof num === 'string') {
                    // If it's already formatted, return as is
                    if (num.includes(',')) return num;
                    // If it's a string number, convert and format
                    return parseFloat(num).toFixed(2);
                }
                // If it's a number, format it
                return (num || 0).toFixed(2);
            };

            // Populate totals
            document.getElementById('receipt-subtotal').textContent = `Rs. ${data.subtotal ? formatNumber(data.subtotal) : '0.00'}`;
            document.getElementById('receipt-total').textContent = `Rs. ${data.total ? formatNumber(data.total) : '0.00'}`;
            
            // Populate payment details
            document.getElementById('payment-method-display').textContent = selectedPaymentMethod;
            
            // Show/hide payment details based on method
            const cashDetails = document.getElementById('cash-payment-details');
            
            if (selectedPaymentMethod === 'CASH' || selectedPaymentMethod === 'CARD & CASH') {
                cashDetails.style.display = 'block';
                document.getElementById('amount-paid-display').textContent = `Rs. ${formatNumber(data.customer_payment || customerPayment)}`;
                document.getElementById('balance-display-receipt').textContent = `Rs. ${formatNumber(data.balance || 0)}`;
            } else {
                cashDetails.style.display = 'none';
            }
        }

        // Start new order function
        function startNewOrder() {
            // Clear cart
            cart = [];
            updateCartDisplay();
            
            // Reset payment
            document.getElementById('customer-payment').value = '0';
            calculateBalance();
            
            // Hide receipt modal
            bootstrap.Modal.getInstance(document.getElementById('receiptModal')).hide();
        }

        // Download receipt as PDF
        function downloadReceiptPDF() {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [80, 200] // Receipt paper size
            });

            // Get stored cart data or use current cart
            const cartData = window.lastSaleData ? window.lastSaleData.cart : cart;
            const paymentMethod = window.lastSaleData ? window.lastSaleData.paymentMethod : selectedPaymentMethod;
            const customerPaymentAmount = window.lastSaleData ? window.lastSaleData.customerPayment : parseFloat(document.getElementById('customer-payment').value) || 0;
            
            // Calculate totals
            const subtotal = cartData.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const balance = customerPaymentAmount - subtotal;

            // Get receipt data with detailed information
            const receiptData = {
                receiptNo: document.getElementById('receipt-no-display') ? document.getElementById('receipt-no-display').textContent : document.getElementById('receipt-no').textContent,
                userName: '{{ Auth::user()->name }}',
                date: new Date().toLocaleDateString('en-GB'),
                time: new Date().toLocaleTimeString('en-GB', { hour12: false }),
                subtotal: `Rs. ${subtotal.toFixed(2)}`,
                total: `Rs. ${subtotal.toFixed(2)}`,
                paymentMethod: paymentMethod,
                showCashDetails: paymentMethod === 'CASH' || paymentMethod === 'CARD & CASH',
                amountPaid: `Rs. ${customerPaymentAmount.toFixed(2)}`,
                balance: `Rs. ${balance.toFixed(2)}`,
                items: cartData.map(item => ({
                    name: item.name,
                    quantity: item.quantity,
                    unitPrice: item.price,
                    subtotal: item.price * item.quantity
                }))
            };

            // PDF generation code
            let yPosition = 10;
            const lineHeight = 3.5;
            const pageWidth = 80;
            
            // Add RB logo circle
            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'bold');
            pdf.circle(pageWidth/2, yPosition + 5, 8);
            pdf.text('RB', pageWidth/2, yPosition + 7, { align: 'center' });
            yPosition += 18;
            
            // Header - Company Name
            pdf.setFontSize(14);
            pdf.setFont('courier', 'bold');
            pdf.text('RAVON BAKERS', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 6;
            
            pdf.setFontSize(10);
            pdf.setFont('courier', 'normal');
            pdf.text('Restaurant & Bakery', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 5;
            
            pdf.setFontSize(8);
            pdf.text('Address: 282/A 2, Kaduwela', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 4;
            pdf.text('Phone: 076 200 6007', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 8;
            
            // Draw thick line
            pdf.setLineWidth(0.5);
            pdf.line(5, yPosition, pageWidth-5, yPosition);
            yPosition += 6;
            
            // Receipt info section
            pdf.setFontSize(9);
            pdf.setFont('courier', 'normal');
            
            // Receipt details with proper spacing
            pdf.text('RECEIPT NO:', 5, yPosition);
            pdf.text(receiptData.receiptNo, pageWidth-5, yPosition, { align: 'right' });
            yPosition += 5;
            
            pdf.text('USER:', 5, yPosition);
            pdf.text(receiptData.userName, pageWidth-5, yPosition, { align: 'right' });
            yPosition += 5;
            
            pdf.text('DATE:', 5, yPosition);
            pdf.text(receiptData.date, pageWidth-5, yPosition, { align: 'right' });
            yPosition += 5;
            
            pdf.text('TIME:', 5, yPosition);
            pdf.text(receiptData.time, pageWidth-5, yPosition, { align: 'right' });
            yPosition += 8;
            
            // Items section header
            pdf.setLineDashPattern([1, 1], 0);
            pdf.line(5, yPosition, pageWidth-5, yPosition);
            pdf.setLineDashPattern([], 0);
            yPosition += 6;
            
            // Items details
            receiptData.items.forEach((item, index) => {
                // Item name (bold)
                pdf.setFont('courier', 'bold');
                pdf.setFontSize(9);
                pdf.text(item.name, 5, yPosition);
                yPosition += 5;
                
                // Quantity x Unit Price = Subtotal
                pdf.setFont('courier', 'normal');
                pdf.setFontSize(8);
                const qtyPriceText = `${item.quantity} x Rs. ${item.unitPrice.toFixed(2)}`;
                const subtotalText = `Rs. ${item.subtotal.toFixed(2)}`;
                
                pdf.text(qtyPriceText, 5, yPosition);
                pdf.text(subtotalText, pageWidth-5, yPosition, { align: 'right' });
                yPosition += 6;
                
                // Add small gap between items
                if (index < receiptData.items.length - 1) {
                    yPosition += 2;
                }
            });
            
            // Dotted line before totals
            pdf.setLineDashPattern([1, 1], 0);
            pdf.line(5, yPosition, pageWidth-5, yPosition);
            pdf.setLineDashPattern([], 0);
            yPosition += 6;
            
            // Totals section
            pdf.setFont('courier', 'normal');
            pdf.setFontSize(9);
            pdf.text('Sub Total:', 5, yPosition);
            pdf.text(receiptData.subtotal, pageWidth-5, yPosition, { align: 'right' });
            yPosition += 6;
            
            // TOTAL in bold
            pdf.setFont('courier', 'bold');
            pdf.setFontSize(11);
            pdf.text('TOTAL:', 5, yPosition);
            pdf.text(receiptData.total, pageWidth-5, yPosition, { align: 'right' });
            yPosition += 8;
            
            // Dotted line before payment
            pdf.setLineDashPattern([1, 1], 0);
            pdf.line(5, yPosition, pageWidth-5, yPosition);
            pdf.setLineDashPattern([], 0);
            yPosition += 6;
            
            // Payment information section
            pdf.setFontSize(9);
            pdf.setFont('courier', 'normal');
            pdf.text('Payment Method:', 5, yPosition);
            pdf.text(receiptData.paymentMethod, pageWidth-5, yPosition, { align: 'right' });
            yPosition += 6;
            
            // Cash payment details if applicable
            if (receiptData.showCashDetails) {
                pdf.text('Amount Paid:', 5, yPosition);
                pdf.text(receiptData.amountPaid, pageWidth-5, yPosition, { align: 'right' });
                yPosition += 5;
                
                pdf.text('Balance:', 5, yPosition);
                pdf.text(receiptData.balance, pageWidth-5, yPosition, { align: 'right' });
                yPosition += 6;
            }
            
            // Bottom dotted line
            pdf.setLineDashPattern([1, 1], 0);
            pdf.line(5, yPosition, pageWidth-5, yPosition);
            pdf.setLineDashPattern([], 0);
            yPosition += 8;
            
            // Footer
            pdf.setFontSize(8);
            pdf.text('Thank you for visiting', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 4;
            pdf.setFont('courier', 'bold');
            pdf.text('RAVON RESTAURANT', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 4;
            pdf.setFont('courier', 'normal');
            pdf.text('Come again!', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 8;
            
            pdf.setFontSize(6);
            pdf.text('System by SKM Labs', pageWidth/2, yPosition, { align: 'center' });
            
            // Generate filename
            const filename = `Receipt_${receiptData.receiptNo}_${new Date().toISOString().slice(0,10)}.pdf`;
            
            // Download the PDF
            pdf.save(filename);
            
            // Show success message
            showSuccess('Receipt PDF downloaded successfully!');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('POS page loaded, initializing...');
            
            // Initialize cart if not already done
            if (!cart) {
                cart = [];
            }
            
            // Check if this is a new order (coming from cleared session)
            const urlParams = new URLSearchParams(window.location.search);
            const isClearSession = urlParams.get('clear') === '1';
            
            if (isClearSession) {
                // Clear all cart and payment data for fresh start
                cart = [];
                selectedPaymentMethod = 'CASH';
                customerPayment = 0;
                
                // Clear browser storage
                if (typeof(Storage) !== "undefined") {
                    localStorage.removeItem('pos_cart');
                    localStorage.removeItem('pos_customer_payment');
                    localStorage.removeItem('pos_selected_payment_method');
                    localStorage.removeItem('pos_receipt_no');
                    sessionStorage.clear();
                }
                
                // Update the receipt number display with fresh number
                const today = new Date();
                const dateStr = today.getFullYear().toString().substr(-2) + 
                               String(today.getMonth() + 1).padStart(2, '0') + 
                               String(today.getDate()).padStart(2, '0');
                document.getElementById('receipt-no').textContent = 'RCP' + dateStr + '0001';
                
                // Clean URL by removing the clear parameter
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            const cashInputSection = document.getElementById('cash-input-section');
            if (selectedPaymentMethod === 'CASH') {
                cashInputSection.classList.add('show');
            }
            
            // Update time every second
            setInterval(updateTime, 1000);
            updateTime();
            
            // Initialize payment display
            document.getElementById('customer-payment').value = '0';
            
            // Add keyboard shortcuts for cash input
            document.addEventListener('keydown', function(event) {
                // Only apply shortcuts when keyboard input is enabled and cash input section is visible
                const cashSection = document.getElementById('cash-input-section');
                const cashInput = document.getElementById('customer-payment');
                
                if (keyboardInputEnabled && cashSection && cashSection.classList.contains('show') && 
                    document.activeElement !== cashInput) {
                    
                    // Number keys (0-9)
                    if (event.keyCode >= 48 && event.keyCode <= 57) {
                        event.preventDefault();
                        const digit = event.key;
                        addToPayment(digit);
                        return;
                    }
                    
                    // Numpad keys (0-9)
                    if (event.keyCode >= 96 && event.keyCode <= 105) {
                        event.preventDefault();
                        const digit = (event.keyCode - 96).toString();
                        addToPayment(digit);
                        return;
                    }
                    
                    // Decimal point
                    if (event.keyCode === 190 || event.keyCode === 110) {
                        event.preventDefault();
                        addToPayment('.');
                        return;
                    }
                    
                    // Clear/Delete/Escape
                    if (event.keyCode === 46 || event.keyCode === 8 || event.keyCode === 27) {
                        event.preventDefault();
                        clearPayment();
                        return;
                    }
                    
                    // Enter to focus on input for direct typing
                    if (event.keyCode === 13) {
                        event.preventDefault();
                        cashInput.focus();
                        cashInput.select();
                        return;
                    }
                }
            });
            
            // Add input focus management
            const cashInput = document.getElementById('customer-payment');
            cashInput.addEventListener('blur', function() {
                // Ensure value is valid when leaving input
                if (this.value === '' || isNaN(parseFloat(this.value))) {
                    this.value = '0';
                    calculateBalance();
                }
            });
            
            // Force initial update of cart display and totals
            updateCartDisplay();
            updateTotals();
            calculateBalance();
            
            console.log('Initial cart state:', cart);
            console.log('Initial total element text:', document.getElementById('total')?.textContent);
        });
    </script>
</body>
</html>