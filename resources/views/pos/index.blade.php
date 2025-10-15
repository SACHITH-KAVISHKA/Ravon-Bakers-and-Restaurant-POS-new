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
        html {
            scroll-behavior: smooth;
        }

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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
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

        .cart-toggle-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            display: none;
        }

        .cart-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .cart-toggle-btn i {
            margin-right: 5px;
        }

        .header-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .header-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .pos-content {
            display: flex;
            flex: 1;
            height: calc(100vh - 60px);
            /* Adjusted for single header */
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
            width: 480px;
            background: #fff;
            border-left: 2px solid #dee2e6;
            display: flex;
            flex-direction: column;
            padding: 0;
            position: relative;
            /* allow absolute positioning of footer button */
            height: calc(100vh - 60px);
            /* fill available viewport height under header */
            overflow: hidden;
            /* keep scrolling inside payment-section */
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        .item-name {
            font-size: 14px;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .item-price {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
        }

        /* Stock Status Styles */
        .stock-info {
            margin-top: 8px;
            font-size: 11px;
        }

        .stock-available {
            color: #28a745;
            font-weight: 600;
        }

        .stock-low {
            color: #ffc107;
            font-weight: 600;
        }

        .stock-not-in-branch {
            color: #6c757d;
            font-weight: 600;
        }

        .item-card.available {
            border-left: 4px solid #28a745;
        }

        .item-card.low-stock {
            border-left: 4px solid #ffc107;
        }

        .item-card.not-in-branch {
            border-left: 4px solid #6c757d;
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
            height: calc(100vh - 550px);
            min-height: 200px;
            max-height: 350px;
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
            position: absolute;
            bottom: 80px;
            left: 12px;
            right: 12px;
            z-index: 40;
            border-radius: 6px;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
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
            display: none;
            /* Hidden by default */
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin: 10px 0;
            flex-shrink: 0;
        }

        .number-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 8px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 50px;
            position: relative;
            outline: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .number-btn:hover {
            background: #0056b3;
            transform: scale(1.02);
        }

        .number-btn:active,
        .number-btn.pressed {
            background: #004085;
            transform: scale(0.98);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
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
            display: none;
            /* Hidden by default */
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
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            max-height: calc(100vh - 60px);
            padding-bottom: 120px;
            /* give room for the checkout button */
            scrollbar-width: thin;
            scrollbar-color: #ccc #f0f0f0;
            scroll-behavior: smooth;
            /* Enable smooth scrolling */
            scroll-padding-top: 20px;
            /* Add padding when scrolling to targets */
        }

        /* Custom scrollbar for webkit browsers */
        .payment-section::-webkit-scrollbar {
            width: 6px;
        }

        .payment-section::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 3px;
        }

        .payment-section::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .payment-section::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }

        .payment-methods {
            display: none;
            /* Hidden by default */
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 12px;
            flex-shrink: 0;
        }

        .payment-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 15px 8px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            min-height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
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
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #ffeaa7;
            display: none;
            flex-shrink: 0;
        }

        .cash-input-section.show {
            display: block;
        }

        /* Card Input Section Styles */
        .card-input-section {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #b3d9ff;
            display: none;
            flex-shrink: 0;
        }

        .card-input-section.show {
            display: block;
        }

        .card-input-group {
            margin-bottom: 4px;
        }

        .card-input-label {
            font-size: 11px;
            font-weight: 600;
            color: #0056b3;
            margin-bottom: 4px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #007bff;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            color: #0056b3;
            background: #f8f9fa;
        }

        .card-input:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background: #fff;
        }

        .card-input:hover {
            border-color: #0056b3;
        }

        /* Active Input Indicator Styles */
        .active-input-indicator {
            display: none;
            margin-bottom: 15px;
            flex-shrink: 0;
            /* Prevent shrinking */
        }

        .active-input-indicator.show {
            display: block;
        }

        .input-selector {
            display: flex;
            gap: 5px;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .input-select-btn {
            flex: 1;
            background: #fff;
            color: #6c757d;
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 40px;
        }

        .input-select-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .input-select-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.25);
        }

        .input-select-btn.active:hover {
            background: #0056b3;
            border-color: #0056b3;
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
            display: none;
            /* Hidden by default */
            background: #d1ecf1;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            color: #0c5460;
            font-size: 14px;
            margin: 15px 0;
            border: 2px solid #bee5eb;
            flex-shrink: 0;
        }

        .checkout-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            width: calc(100% - 24px);
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
            /* Prevent shrinking */
            margin: 1px;
            /* margin inside payment-panel */
            min-height: 50px;
            /* Ensure good touch target */
            position: absolute;
            /* position within payment-panel */
            left: 12px;
            right: 12px;
            bottom: 12px;
            z-index: 50;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.12);
            /* Add shadow to make it stand out */
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

        .print-btn,
        .new-order-btn {
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

        .print-btn:hover,
        .new-order-btn:hover {
            transform: translateY(-2px);
            filter: brightness(110%);
        }

        /* Payment Modal Styles */
        .payment-type-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .payment-type-btn {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            padding: 12px 16px;
            border-radius: 6px;
            color: #495057;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
        }

        .payment-type-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .payment-type-btn.active {
            background: #007bff;
            border-color: #007bff;
            color: white;
        }

        .modal-number-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            border-radius: 4px;
            width: 100%;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .modal-number-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .modal-number-btn:active {
            background: #dee2e6;
            transform: scale(0.95);
        }

        .modal-number-btn.clear-btn {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .modal-number-btn.clear-btn:hover {
            background: #c82333;
            border-color: #c82333;
        }

        .modal-number-btn.enter-btn {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .modal-number-btn.enter-btn:hover {
            background: #218838;
            border-color: #218838;
        }

        .payment-summary {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
        }

        .summary-table {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 14px;
        }

        .summary-row.total-row {
            font-weight: bold;
            font-size: 16px;
            color: #007bff;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            padding: 8px 0;
            margin: 8px 0;
        }

        .summary-row.balance-row {
            font-weight: bold;
            font-size: 16px;
            color: #28a745;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
            margin-top: 8px;
        }

        .payment-amount-input {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            background: #fff3cd;
            border-color: #ffeaa7;
        }

        .payment-amount-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background: white;
        }

        .card-type-inputs .form-control {
            font-size: 12px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .pos-container {
                height: 100vh;
                overflow: hidden;
            }

            .pos-header {
                padding: 8px 15px;
                font-size: 16px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .header-left {
                font-size: 12px;
                gap: 8px;
                flex-wrap: wrap;
            }

            .header-center {
                font-size: 16px;
                order: -1;
                flex: 1 1 100%;
                text-align: center;
                margin-bottom: 5px;
            }

            .header-right {
                gap: 8px;
            }

            .header-btn {
                padding: 4px 8px;
                font-size: 11px;
            }

            .pos-content {
                flex-direction: column;
                height: calc(100vh - 80px);
            }

            .categories-panel {
                width: 100%;
                height: 120px;
                border-right: none;
                border-bottom: 2px solid #dee2e6;
                overflow-x: auto;
                overflow-y: hidden;
            }

            .category-list {
                display: flex;
                flex-direction: row;
                padding: 0 10px;
                height: 100%;
            }

            .category-item {
                flex: 0 0 auto;
                width: 120px;
                border-bottom: none;
                border-right: 1px solid #e9ecef;
                padding: 10px;
                white-space: nowrap;
                font-size: 12px;
            }

            .category-item:last-child {
                border-right: none;
            }

            .items-panel {
                flex: 1;
                padding: 15px;
                overflow-y: auto;
            }

            .items-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 10px;
            }

            .item-card {
                padding: 10px;
                border-radius: 8px;
            }

            .item-name {
                font-size: 12px;
            }

            .item-price {
                font-size: 13px;
            }

            .payment-panel {
                width: 100%;
                height: 400px;
                border-left: none;
                border-top: 2px solid #dee2e6;
                position: fixed;
                bottom: 0;
                left: 0;
                z-index: 1000;
                background: white;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            }

            .payment-panel.show {
                display: flex;
            }

            .cart-section {
                max-height: 200px;
                overflow-y: auto;
            }

            .cart-item {
                padding: 8px;
                font-size: 12px;
            }

            .payment-section {
                padding: 15px;
            }

            .payment-buttons {
                flex-wrap: wrap;
                gap: 8px;
            }

            .payment-btn {
                flex: 1 1 calc(50% - 8px);
                min-width: 120px;
                padding: 12px 8px;
                font-size: 14px;
            }

            .payment-amount-input {
                font-size: 16px;
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .items-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 8px;
            }

            .item-card {
                padding: 8px;
            }

            .payment-btn {
                flex: 1 1 calc(50% - 6px);
                min-width: 100px;
                padding: 10px 6px;
                font-size: 13px;
            }

            .categories-panel {
                height: 100px;
            }

            .category-item {
                width: 100px;
                padding: 8px 6px;
                font-size: 11px;
            }
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
            <button class="cart-toggle-btn d-md-none" id="cart-toggle-btn" onclick="toggleCart()">
                <i class="bi bi-cart3"></i>
                <span id="cart-count">0</span>
            </button>
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
                    <div class="item-card"
                        data-category="{{ $category }}"
                        data-item-id="{{ $item->id }}"
                        data-item-name="{{ $item->item_name }}"
                        data-item-price="{{ $item->price }}"
                        onclick="addToCartFromCard(this)">
                        <div class="item-name">{{ $item->item_name }}</div>
                        <div class="item-price">LKR {{ number_format($item->price, 2) }}</div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>

            <!-- Right Panel - Cart & Payment -->
            <div class="payment-panel d-none d-md-flex">
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
                        <span id="subtotal">LKR 0.00</span>
                    </div> -->
                    <div class="total-row grand-total">
                        <span>TOTAL</span>
                        <span id="total">LKR 0.00</span>
                    </div>
                </div>

                <!-- Checkout Button -->
                <button type="button" class="checkout-btn" id="checkout-btn" onclick="openPaymentModal()" disabled>
                    <i class="bi bi-credit-card"></i> Process Payment
                </button>
            </div>
        </div>
    </div>



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

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="bi bi-credit-card"></i> Payment Processing
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Left Column - Payment Type Selection -->
                        <div class="col-md-5">
                            <div class="payment-types-section">
                                <h6 class="fw-bold mb-3">SELECT PAYMENT TYPE</h6>
                                <div class="payment-type-buttons">
                                    <button type="button" class="payment-type-btn" data-method="CASH" onclick="selectModalPaymentMethod('CASH', this)">
                                        CASH
                                    </button>
                                    <button type="button" class="payment-type-btn" data-method="CARD" onclick="selectModalPaymentMethod('CARD', this)">
                                        CARD
                                    </button>
                                    <button type="button" class="payment-type-btn" data-method="CARD & CASH" onclick="selectModalPaymentMethod('CARD & CASH', this)">
                                        CARD & CASH
                                    </button>
                                    <button type="button" class="payment-type-btn" data-method="CREDIT" onclick="selectModalPaymentMethod('CREDIT', this)">
                                        CREDIT
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Number Pad -->
                            <div class="modal-number-pad mt-4">
                                <div class="row g-2">
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('2000')">2000</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('5000')">5000</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('7')">7</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('500')">500</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('1000')">1000</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('8')">8</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('100')">100</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('200')">200</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('9')">9</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('4')">4</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('5')">5</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('6')">6</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('1')">1</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('2')">2</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('3')">3</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('20')">20</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('50')">50</button></div>
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('0')">0</button></div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-4"><button class="modal-number-btn" onclick="addToModalActiveInput('.')">.</button></div>
                                    <div class="col-4"><button class="modal-number-btn clear-btn" onclick="clearModalActiveInput()">C1</button></div>
                                    <div class="col-4"><button class="modal-number-btn enter-btn" onclick="processModalPayment()">Enter</button></div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Payment Details -->
                        <div class="col-md-7">
                            <!-- Removed detailed card/type inputs per request -->

                            <!-- Simplified Payment Summary -->
                            <div class="payment-summary">
                                <div class="summary-table">
                                    <div class="summary-row">
                                        <span>Sub Total</span>
                                        <span id="modal-subtotal">0.00</span>
                                    </div>
                                    <div class="summary-row total-row">
                                        <span>Total ——></span>
                                        <span id="modal-total">0.00</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Card</span>
                                        <span id="modal-card-amount">0.00</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Cash</span>
                                        <span id="modal-cash-amount">0.00</span>
                                    </div>
                                    <div class="summary-row balance-row">
                                        <span>Balance ——></span>
                                        <span id="modal-balance">0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Input -->
                            <div class="payment-input-modal mt-3">
                                <!-- Cash Input (shown for CASH and CASH & CARD) -->
                                <div class="input-group mb-2" id="modal-cash-input-group" style="display: none;">
                                    <span class="input-group-text">Cash Amount</span>
                                    <input type="number" class="form-control payment-amount-input" id="modal-cash-input" placeholder="0.00" step="0.01" readonly onclick="setActiveModalInput('cash')" style="cursor: pointer;">
                                </div>
                                
                                <!-- Card Input (shown for CARD and CASH & CARD) -->
                                <div class="input-group" id="modal-card-input-group" style="display: none;">
                                    <span class="input-group-text">Card Amount</span>
                                    <input type="number" class="form-control payment-amount-input" id="modal-card-input" placeholder="0.00" step="0.01" readonly onclick="setActiveModalInput('card')" style="cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn btn-success" onclick="processModalPayment()">
                        <i class="bi bi-check-circle"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let cart = []; // Initialize empty cart
        let selectedPaymentMethod = null; // No payment method selected by default
        let customerPayment = 0;
        let cardPayment = 0;
        let activeInput = 'customer'; // Track which input is currently active

        // Add item to cart from card click
        function addToCartFromCard(card) {
            const itemId = parseInt(card.dataset.itemId);
            const itemName = card.dataset.itemName;
            const price = parseFloat(card.dataset.itemPrice);

            if (isNaN(price) || price <= 0) {
                console.error('Invalid price:', card.dataset.itemPrice);
                showError('Invalid item price');
                return;
            }

            addToCart(itemId, itemName, price);
        }

        // Add item to cart
        function addToCart(itemId, itemName, price) {
            const existingItem = cart.find(item => item.id === itemId);
            const itemPrice = parseFloat(price) || 0;

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    quantity: 1
                });
            }

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
                item.quantity = newQuantity;
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
                                <div class="cart-item-price">LKR ${itemPrice.toFixed(2)} each</div>
                            </div>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="updateQuantity(${item.id}, ${itemQuantity - 1})">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="qty-input" value="${itemQuantity}"
                                       onchange="updateQuantity(${item.id}, this.value)" min="1">
                                <button type="button" class="qty-btn plus" onclick="updateQuantity(${item.id}, ${itemQuantity + 1})">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <div class="cart-total-price">LKR ${totalPrice.toFixed(2)}</div>
                            <button type="button" class="remove-btn" onclick="removeFromCart(${item.id})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>`;
                });
                cartContainer.innerHTML = html;
                checkoutBtn.disabled = false;
            }

            updateTotals();
            
            // Update mobile cart count
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                const totalItems = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                cartCountElement.textContent = totalItems;
            }
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
                subtotalElement.textContent = `LKR ${subtotal.toFixed(2)}`;
            }

            // Update total element
            const totalElement = document.getElementById('total');
            if (totalElement) {
                totalElement.textContent = `LKR ${total.toFixed(2)}`;
            }

            // Update checkout button based on cart
            updateCheckoutButton();
        }

        // Update checkout button state and text
        function updateCheckoutButton() {
            const checkoutBtn = document.getElementById('checkout-btn');

            if (cart.length === 0) {
                checkoutBtn.disabled = true;
                checkoutBtn.innerHTML = '<i class="bi bi-cart-x"></i> Add Items to Cart';
            } else {
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = '<i class="bi bi-credit-card"></i> Process Payment';
            }
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

        // Helper function kept as stub - modal UI handles its own scrolling
        function scrollToPaymentInput() {
            // No-op: modal handles its own scrolling
        }

        // Force scroll to number pad - no-op for modal implementation
        function forceScrollToNumberPad() {
            // No-op: modal handles its own scrolling
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

        // Toggle cart on mobile
        function toggleCart() {
            const paymentPanel = document.querySelector('.payment-panel');
            if (window.innerWidth <= 768) {
                paymentPanel.classList.toggle('show');
            }
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
                // Reset modal payment state too
                modalSelectedPaymentMethod = null;
                modalCustomerPayment = 0;
                modalCardPayment = 0;
                updateCartDisplay();
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

        // Modal variables
        let modalSelectedPaymentMethod = null;
        let modalCustomerPayment = 0;
        let modalCardPayment = 0;

        // Open payment modal
        function openPaymentModal() {
            if (cart.length === 0) {
                showError('Please add items to cart before checkout');
                return;
            }

            // Reset modal state
            modalSelectedPaymentMethod = null;
            modalCustomerPayment = 0;
            modalCardPayment = 0;
            activeModalInput = 'cash'; // Default to cash input
            
            // Clear all payment type buttons
            document.querySelectorAll('.payment-type-btn').forEach(btn => btn.classList.remove('active'));
            
            // Clear and hide input fields
            const cashInput = document.getElementById('modal-cash-input');
            const cardInput = document.getElementById('modal-card-input');
            const cashInputGroup = document.getElementById('modal-cash-input-group');
            const cardInputGroup = document.getElementById('modal-card-input-group');
            
            if (cashInput) cashInput.value = '';
            if (cardInput) cardInput.value = '';
            if (cashInputGroup) cashInputGroup.style.display = 'none';
            if (cardInputGroup) cardInputGroup.style.display = 'none';
            
            // Update modal totals
            updateModalTotals();
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }

        // Select payment method in modal
        function selectModalPaymentMethod(method, button) {
            modalSelectedPaymentMethod = method;
            
            // Update button states
            document.querySelectorAll('.payment-type-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Clear payment amounts
            modalCustomerPayment = 0;
            modalCardPayment = 0;
            
            // Get input elements
            const cashInputGroup = document.getElementById('modal-cash-input-group');
            const cardInputGroup = document.getElementById('modal-card-input-group');
            const cashInput = document.getElementById('modal-cash-input');
            const cardInput = document.getElementById('modal-card-input');
            
            // Clear input values
            cashInput.value = '';
            cardInput.value = '';
            
            // Reset border styles
            cashInput.style.borderColor = '#ced4da';
            cashInput.style.borderWidth = '1px';
            cardInput.style.borderColor = '#ced4da';
            cardInput.style.borderWidth = '1px';
            
            // Show/hide inputs based on payment method
            if (method === 'CASH') {
                cashInputGroup.style.display = 'flex';
                cardInputGroup.style.display = 'none';
                activeModalInput = 'cash';
            } else if (method === 'CARD') {
                cashInputGroup.style.display = 'none';
                cardInputGroup.style.display = 'flex';
                // Auto-fill card amount with total
                const total = getTotalAmount();
                modalCardPayment = total;
                cardInput.value = total.toFixed(2);
                activeModalInput = 'card';
            } else if (method === 'CARD & CASH') {
                cashInputGroup.style.display = 'flex';
                cardInputGroup.style.display = 'flex';
                activeModalInput = 'cash'; // Default to cash input
                // Highlight cash input as active
                cashInput.style.borderColor = '#0d6efd';
                cashInput.style.borderWidth = '2px';
            } else if (method === 'CREDIT') {
                cashInputGroup.style.display = 'none';
                cardInputGroup.style.display = 'none';
            }
            
            updateModalTotals();
        }

        // Track which input is active for CASH & CARD mode
        let activeModalInput = 'cash'; // 'cash' or 'card'

        // Set active input for CASH & CARD mode
        function setActiveModalInput(inputType) {
            if (modalSelectedPaymentMethod !== 'CARD & CASH') return;
            
            activeModalInput = inputType;
            
            const cashInput = document.getElementById('modal-cash-input');
            const cardInput = document.getElementById('modal-card-input');
            
            // Visual feedback: add border highlight to active input
            if (cashInput && cardInput) {
                if (inputType === 'cash') {
                    cashInput.style.borderColor = '#0d6efd';
                    cashInput.style.borderWidth = '2px';
                    cardInput.style.borderColor = '#ced4da';
                    cardInput.style.borderWidth = '1px';
                } else {
                    cardInput.style.borderColor = '#0d6efd';
                    cardInput.style.borderWidth = '2px';
                    cashInput.style.borderColor = '#ced4da';
                    cashInput.style.borderWidth = '1px';
                }
            }
        }

        // Add number to modal active input
        function addToModalActiveInput(value) {
            if (!modalSelectedPaymentMethod) return;
            
            let paymentInput;
            
            // Determine which input to use based on payment method
            if (modalSelectedPaymentMethod === 'CASH') {
                paymentInput = document.getElementById('modal-cash-input');
            } else if (modalSelectedPaymentMethod === 'CARD') {
                paymentInput = document.getElementById('modal-card-input');
            } else if (modalSelectedPaymentMethod === 'CARD & CASH') {
                // Use active input for CASH & CARD mode
                paymentInput = activeModalInput === 'cash' 
                    ? document.getElementById('modal-cash-input')
                    : document.getElementById('modal-card-input');
            } else if (modalSelectedPaymentMethod === 'CREDIT') {
                // For credit, no input needed (balance is auto-calculated)
                return;
            }
            
            if (!paymentInput) return;
            
            const currentValue = paymentInput.value || '0';
            
            if (value === '.') {
                if (!currentValue.includes('.')) {
                    paymentInput.value = currentValue + value;
                }
            } else {
                if (currentValue === '0') {
                    paymentInput.value = value;
                } else {
                    paymentInput.value = currentValue + value;
                }
            }
            
            // Update the payment amounts
            if (modalSelectedPaymentMethod === 'CASH' || (modalSelectedPaymentMethod === 'CARD & CASH' && activeModalInput === 'cash')) {
                modalCustomerPayment = parseFloat(paymentInput.value) || 0;
            }
            
            if (modalSelectedPaymentMethod === 'CARD' || (modalSelectedPaymentMethod === 'CARD & CASH' && activeModalInput === 'card')) {
                modalCardPayment = parseFloat(paymentInput.value) || 0;
            }
            
            updateModalTotals();
        }

        // Clear modal active input
        function clearModalActiveInput() {
            if (!modalSelectedPaymentMethod) return;
            
            if (modalSelectedPaymentMethod === 'CASH') {
                document.getElementById('modal-cash-input').value = '';
                modalCustomerPayment = 0;
            } else if (modalSelectedPaymentMethod === 'CARD') {
                // For CARD, reset to total amount
                const total = getTotalAmount();
                document.getElementById('modal-card-input').value = total.toFixed(2);
                modalCardPayment = total;
            } else if (modalSelectedPaymentMethod === 'CARD & CASH') {
                // Clear the active input
                if (activeModalInput === 'cash') {
                    document.getElementById('modal-cash-input').value = '';
                    modalCustomerPayment = 0;
                } else {
                    document.getElementById('modal-card-input').value = '';
                    modalCardPayment = 0;
                }
            } else if (modalSelectedPaymentMethod === 'CREDIT') {
                // Nothing to clear for credit
                modalCustomerPayment = 0;
                modalCardPayment = 0;
            }
            
            updateModalTotals();
        }

        // Update modal totals
        function updateModalTotals() {
            const total = getTotalAmount();
            
            // Update subtotal and total
            document.getElementById('modal-subtotal').textContent = total.toFixed(2);
            document.getElementById('modal-total').textContent = total.toFixed(2);
            
            let balance = 0;
            
            // Calculate based on payment method
            if (modalSelectedPaymentMethod === 'CASH') {
                // CASH: show cash amount and calculate balance
                document.getElementById('modal-cash-amount').textContent = modalCustomerPayment.toFixed(2);
                document.getElementById('modal-card-amount').textContent = '0.00';
                balance = modalCustomerPayment - total;
            } else if (modalSelectedPaymentMethod === 'CARD') {
                // CARD: card amount equals total, balance is 0
                document.getElementById('modal-cash-amount').textContent = '0.00';
                document.getElementById('modal-card-amount').textContent = modalCardPayment.toFixed(2);
                balance = 0;
            } else if (modalSelectedPaymentMethod === 'CARD & CASH') {
                // CARD & CASH: show both amounts and calculate balance
                document.getElementById('modal-cash-amount').textContent = modalCustomerPayment.toFixed(2);
                document.getElementById('modal-card-amount').textContent = modalCardPayment.toFixed(2);
                const totalPaid = modalCustomerPayment + modalCardPayment;
                balance = totalPaid - total;
            } else if (modalSelectedPaymentMethod === 'CREDIT') {
                // CREDIT: balance equals total (credit balance)
                document.getElementById('modal-cash-amount').textContent = '0.00';
                document.getElementById('modal-card-amount').textContent = '0.00';
                balance = total; // Store as credit balance
            }
            
            document.getElementById('modal-balance').textContent = balance.toFixed(2);
        }

        // Process modal payment
        function processModalPayment() {
            if (!modalSelectedPaymentMethod) {
                showError('Please select a payment method');
                return;
            }

            if (cart.length === 0) {
                showError('Cart is empty');
                return;
            }

            const total = getTotalAmount();
            const totalPaid = modalCustomerPayment + modalCardPayment;
            let balance = 0;
            let creditBalance = 0;

            // Calculate balance and validate based on payment method
            if (modalSelectedPaymentMethod === 'CASH') {
                if (modalCustomerPayment < total) {
                    showError('Insufficient cash payment amount');
                    return;
                }
                balance = modalCustomerPayment - total;
            } else if (modalSelectedPaymentMethod === 'CARD') {
                // Card payment should equal total, balance is 0
                balance = 0;
            } else if (modalSelectedPaymentMethod === 'CARD & CASH') {
                if (totalPaid < total) {
                    showError('Insufficient payment amount (cash + card)');
                    return;
                }
                balance = totalPaid - total;
            } else if (modalSelectedPaymentMethod === 'CREDIT') {
                // For credit, the total becomes the credit balance
                creditBalance = total;
                balance = total; // Show as balance
            }

            // Prepare order data
            const orderData = {
                items: cart,
                payment_method: modalSelectedPaymentMethod,
                customer_payment: modalCustomerPayment,
                card_payment: modalCardPayment,
                balance: balance,
                credit_balance: creditBalance, // Store credit balance for CREDIT method
                total: total
            };

            // Process the payment
            const printBtn = document.querySelector('.btn-success');
            printBtn.disabled = true;
            printBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

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
                            paymentMethod: modalSelectedPaymentMethod,
                            customerPayment: modalCustomerPayment,
                            cardPayment: modalCardPayment,
                            receiptData: data,
                            backendData: data
                        };

                        // Hide payment modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                        modal.hide();

                        // Generate PDF receipt
                        downloadReceiptPDF();

                        // Clear the cart and start new order
                        cart = [];
                        updateCartDisplay();
                    } else {
                        showError(data.message || 'Error processing payment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Error processing payment. Please try again.');
                })
                .finally(() => {
                    printBtn.disabled = false;
                    printBtn.innerHTML = '<i class="bi bi-check-circle"></i> Print Receipt';
                });
        }

        // Clear all orders function
        function clearAllOrders() {
            cart = [];
            selectedPaymentMethod = null;
            
            // Reset modal state
            modalSelectedPaymentMethod = null;
            modalCustomerPayment = 0;
            modalCardPayment = 0;

            updateCartDisplay();
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
                            <div class="item-qty-price">${item.quantity} x LKR ${item.price.toFixed(2)}</div>
                        </div>
                        <div>LKR ${(item.price * item.quantity).toFixed(2)}</div>
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
            document.getElementById('receipt-subtotal').textContent = `LKR ${data.subtotal ? formatNumber(data.subtotal) : '0.00'}`;
            document.getElementById('receipt-total').textContent = `LKR ${data.total ? formatNumber(data.total) : '0.00'}`;

            // Populate payment details
            document.getElementById('payment-method-display').textContent = data.payment_method || selectedPaymentMethod;

            // Show/hide payment details based on method
            const cashDetails = document.getElementById('cash-payment-details');
            const paymentMethod = data.payment_method || selectedPaymentMethod;

            if (paymentMethod === 'CASH') {
                cashDetails.style.display = 'block';
                document.getElementById('amount-paid-display').textContent = `LKR ${formatNumber(data.customer_payment || customerPayment)}`;
                document.getElementById('balance-display-receipt').textContent = `LKR ${formatNumber(data.balance || 0)}`;
            } else if (paymentMethod === 'CARD & CASH') {
                cashDetails.style.display = 'block';

                // For card & cash, we need to show both payment amounts
                const cardPaymentValue = parseFloat(data.card_payment) || (window.lastSaleData ? window.lastSaleData.cardPayment : (typeof modalCardPayment !== 'undefined' ? modalCardPayment : 0));
                const customerPaymentValue = parseFloat(data.customer_payment) || (window.lastSaleData ? window.lastSaleData.customerPayment : (typeof modalCustomerPayment !== 'undefined' ? modalCustomerPayment : 0));
                const totalPaid = customerPaymentValue + cardPaymentValue;
                const balanceValue = parseFloat(data.balance) || (totalPaid - getTotalAmount());

                // Update display to show detailed breakdown
                const amountDisplay = document.getElementById('amount-paid-display');
                if (amountDisplay) {
                    amountDisplay.innerHTML = `
                        Customer: LKR ${formatNumber(customerPaymentValue)}<br>
                        Card: LKR ${formatNumber(cardPaymentValue)}<br>
                        <strong>Total: LKR ${formatNumber(totalPaid)}</strong>
                    `;
                }

                document.getElementById('balance-display-receipt').textContent = `LKR ${formatNumber(balanceValue)}`;
            } else {
                cashDetails.style.display = 'none';
            }
        }

        // Start new order function
        function startNewOrder() {
            // Clear cart and reset modal payment state
            cart = [];
            selectedPaymentMethod = null;
            modalSelectedPaymentMethod = null;
            modalCustomerPayment = 0;
            modalCardPayment = 0;

            // Reset any payment-type buttons (modal)
            document.querySelectorAll('.payment-type-btn').forEach(btn => btn.classList.remove('active'));

            updateCartDisplay();
        }
        // Download receipt as PDF - DYNAMIC LENGTH VERSION
        function downloadReceiptPDF() {
            try {
                const {
                    jsPDF
                } = window.jspdf;

                // Get stored cart data or use current cart
                const cartData = window.lastSaleData ? window.lastSaleData.cart : cart;
                const paymentMethod = window.lastSaleData ? window.lastSaleData.paymentMethod : selectedPaymentMethod;
                const customerPaymentAmount = window.lastSaleData?.backendData?.customer_payment ?
                    parseFloat(window.lastSaleData.backendData.customer_payment.replace(/,/g, '')) :
                    (window.lastSaleData ? window.lastSaleData.customerPayment : (typeof modalCustomerPayment !== 'undefined' ? modalCustomerPayment : 0));
                const cardPaymentAmount = window.lastSaleData?.backendData?.card_payment ?
                    parseFloat(window.lastSaleData.backendData.card_payment.replace(/,/g, '')) :
                    (window.lastSaleData ? window.lastSaleData.cardPayment : (typeof modalCardPayment !== 'undefined' ? modalCardPayment : 0));

                // Calculate totals from cart
                const subtotal = cartData.reduce((sum, item) => sum + (item.price * item.quantity), 0);

                // Calculate balance based on payment method
                let balance = 0;
                let totalPaid = 0;

                if (paymentMethod === 'CASH') {
                    totalPaid = customerPaymentAmount;
                    balance = customerPaymentAmount - subtotal;
                } else if (paymentMethod === 'CARD & CASH') {
                    totalPaid = customerPaymentAmount + cardPaymentAmount;
                    balance = totalPaid - subtotal;
                } else if (paymentMethod === 'CARD') {
                    totalPaid = subtotal; // For card, amount paid equals total
                    balance = 0;
                } else if (paymentMethod === 'CREDIT') {
                    totalPaid = 0;
                    balance = -subtotal; // Negative balance for credit
                }

                // Build receipt data object from cart
                const receiptData = {
                    receiptNo: window.lastSaleData?.backendData?.receipt_no || document.getElementById('receipt-no').textContent,
                    userName: window.lastSaleData?.backendData?.user_name || '{{ Auth::user()->name }}',
                    date: new Date().toLocaleDateString('en-GB'),
                    time: new Date().toLocaleTimeString('en-GB', {
                        hour12: false
                    }),
                    subtotal: window.lastSaleData?.backendData?.subtotal || subtotal.toFixed(2),
                    total: window.lastSaleData?.backendData?.total || subtotal.toFixed(2),
                    paymentMethod: paymentMethod,
                    showCashDetails: paymentMethod === 'CASH' || paymentMethod === 'CARD & CASH',
                    showCardCashDetails: paymentMethod === 'CARD & CASH',
                    customerPayment: customerPaymentAmount.toFixed(2),
                    cardPayment: cardPaymentAmount.toFixed(2),
                    amountPaid: totalPaid.toFixed(2),
                    balance: window.lastSaleData?.backendData?.balance || balance.toFixed(2),
                    items: cartData.map(item => ({
                        name: item.name,
                        quantity: item.quantity,
                        unitPrice: item.price.toFixed(2),
                        totalPrice: (item.price * item.quantity).toFixed(2)
                    }))
                };

                // Create PDF with fixed width (80mm) and let height be dynamic
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: [80, 297] // Start with A4 height, will extend if needed
                });

                let yPosition = 10;
                const pageWidth = 80;
                const pageHeight = 297; // A4 height in mm
                const leftMargin = 5;
                const rightMargin = 5;
                const maxPageHeight = pageHeight - 20; // Leave margin at bottom

                // Helper function to check if we need a new page
                function checkPageBreak(requiredSpace) {
                    if (yPosition + requiredSpace > maxPageHeight) {
                        pdf.addPage();
                        yPosition = 10;

                        // Add simplified header for continuation pages
                        pdf.setFontSize(10);
                        pdf.setFont('helvetica', 'bold');
                        pdf.circle(pageWidth / 2, yPosition + 5, 6);
                        pdf.text('RB', pageWidth / 2, yPosition + 6, {
                            align: 'center'
                        });
                        yPosition += 14;

                        pdf.setFontSize(12);
                        pdf.setFont('courier', 'bold');
                        pdf.text('RAVON BAKERS', pageWidth / 2, yPosition, {
                            align: 'center'
                        });
                        yPosition += 6;

                        pdf.setFontSize(8);
                        pdf.setFont('courier', 'normal');
                        pdf.text('(Continued)', pageWidth / 2, yPosition, {
                            align: 'center'
                        });
                        yPosition += 8;

                        // Separator line
                        pdf.setLineWidth(0.3);
                        pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
                        yPosition += 6;

                        return true;
                    }
                    return false;
                }

                // Header section - only on first page
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'bold');
                pdf.circle(pageWidth / 2, yPosition + 5, 8);
                pdf.text('RB', pageWidth / 2, yPosition + 7, {
                    align: 'center'
                });
                yPosition += 18;

                pdf.setFontSize(14);
                pdf.setFont('courier', 'bold');
                pdf.text('RAVON BAKERS', pageWidth / 2, yPosition, {
                    align: 'center'
                });
                yPosition += 6;

                pdf.setFontSize(10);
                pdf.setFont('courier', 'normal');
                pdf.text('Restaurant & Bakery', pageWidth / 2, yPosition, {
                    align: 'center'
                });
                yPosition += 5;

                pdf.setFontSize(8);
                pdf.text('Address: 282/A 2, Kaduwela', pageWidth / 2, yPosition, {
                    align: 'center'
                });
                yPosition += 4;
                pdf.text('Phone: 076 200 6007', pageWidth / 2, yPosition, {
                    align: 'center'
                });
                yPosition += 8;

                // Separator line
                pdf.setLineWidth(0.5);
                pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
                yPosition += 6;

                // Receipt information
                pdf.setFontSize(9);
                pdf.setFont('courier', 'normal');

                checkPageBreak(25); // Check space for receipt info block

                pdf.text('RECEIPT NO:', leftMargin, yPosition);
                pdf.text(receiptData.receiptNo, pageWidth - rightMargin, yPosition, {
                    align: 'right'
                });
                yPosition += 5;

                pdf.text('USER:', leftMargin, yPosition);
                pdf.text(receiptData.userName, pageWidth - rightMargin, yPosition, {
                    align: 'right'
                });
                yPosition += 5;

                pdf.text('DATE:', leftMargin, yPosition);
                pdf.text(receiptData.date, pageWidth - rightMargin, yPosition, {
                    align: 'right'
                });
                yPosition += 5;

                pdf.text('TIME:', leftMargin, yPosition);
                pdf.text(receiptData.time, pageWidth - rightMargin, yPosition, {
                    align: 'right'
                });
                yPosition += 8;

                // Items section
                checkPageBreak(15); // Check space for items header

                pdf.setLineWidth(0.3);
                pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
                yPosition += 6;

                // Process each item with automatic page breaks
                receiptData.items.forEach((item, index) => {
                    checkPageBreak(12); // Each item needs about 12mm space

                    // Item name and total price
                    pdf.setFont('courier', 'bold');
                    pdf.setFontSize(9);

                    // Truncate long item names if necessary
                    let itemName = item.name;
                    if (itemName.length > 22) {
                        itemName = itemName.substring(0, 19) + '...';
                    }

                    pdf.text(itemName, leftMargin, yPosition);
                    pdf.text(`LKR ${item.totalPrice}`, pageWidth - rightMargin, yPosition, {
                        align: 'right'
                    });
                    yPosition += 4;

                    // Quantity and unit price
                    pdf.setFont('courier', 'normal');
                    pdf.setFontSize(8);
                    pdf.text(`${item.quantity} x LKR ${item.unitPrice}`, leftMargin + 2, yPosition);
                    yPosition += 6;
                });

                // Totals section
                checkPageBreak(40); // Check space for totals and footer

                yPosition += 2;
                pdf.setLineDashPattern([1, 1], 0);
                pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
                pdf.setLineDashPattern([], 0);
                yPosition += 6;

                pdf.setFont('courier', 'normal');
                pdf.setFontSize(9);
                pdf.text('Sub Total:', leftMargin, yPosition);
                pdf.text(`LKR ${receiptData.subtotal}`, pageWidth - rightMargin, yPosition, {
                    align: 'right'
                });
                yPosition += 6;

                pdf.setFont('courier', 'bold');
                pdf.setFontSize(11);
                pdf.text('TOTAL:', leftMargin, yPosition);
                pdf.text(`LKR ${receiptData.total}`, pageWidth - rightMargin, yPosition, {
                    align: 'right'
                });
                yPosition += 8;

                // Payment information
                pdf.setFontSize(9);
                pdf.setFont('courier', 'normal');
                pdf.text('Payment Method:', leftMargin, yPosition);
                pdf.text(receiptData.paymentMethod, pageWidth - rightMargin, yPosition, {
                    align: 'right'
                });
                yPosition += 6;

                if (receiptData.showCashDetails) {
                    if (receiptData.showCardCashDetails) {
                        // For CARD & CASH payment method
                        pdf.text('Customer Payment:', leftMargin, yPosition);
                        pdf.text(`LKR ${receiptData.customerPayment}`, pageWidth - rightMargin, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;

                        pdf.text('Card Payment:', leftMargin, yPosition);
                        pdf.text(`LKR ${receiptData.cardPayment}`, pageWidth - rightMargin, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;

                        pdf.text('Total Paid:', leftMargin, yPosition);
                        pdf.text(`LKR ${receiptData.amountPaid}`, pageWidth - rightMargin, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;

                        // Calculate balance correctly: (Customer Payment + Card Payment) - Total
                        const calculatedBalance = (parseFloat(receiptData.customerPayment) + parseFloat(receiptData.cardPayment) - parseFloat(receiptData.total)).toFixed(2);
                        pdf.text('Balance:', leftMargin, yPosition);
                        pdf.text(`LKR ${calculatedBalance}`, pageWidth - rightMargin, yPosition, {
                            align: 'right'
                        });
                        yPosition += 6;
                    } else {
                        // For CASH only payment method
                        pdf.text('Amount Paid:', leftMargin, yPosition);
                        pdf.text(`LKR ${receiptData.amountPaid}`, pageWidth - rightMargin, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;

                        // Balance (common for CASH)
                        pdf.text('Balance:', leftMargin, yPosition);
                        pdf.text(`LKR ${receiptData.balance}`, pageWidth - rightMargin, yPosition, {
                            align: 'right'
                        });
                        yPosition += 6;
                    }
                } else if (receiptData.paymentMethod === 'CREDIT') {
                    // For CREDIT payment method
                    pdf.text('Amount Due:', leftMargin, yPosition);
                    pdf.text(`LKR ${receiptData.total}`, pageWidth - rightMargin, yPosition, {
                        align: 'right'
                    });
                    yPosition += 5;

                    pdf.text('Credit Balance:', leftMargin, yPosition);
                    pdf.text(`LKR ${receiptData.balance}`, pageWidth - rightMargin, yPosition, {
                        align: 'right'
                    });
                    yPosition += 6;
                }

                // Footer
                yPosition += 4;
                pdf.setLineDashPattern([1, 1], 0);
                pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
                pdf.setLineDashPattern([], 0);
                yPosition += 8;

                pdf.setFontSize(8);
                pdf.setFont('courier', 'normal');
                pdf.text('Thank you for visiting', pageWidth / 2, yPosition, {
                    align: 'center'
                });
                yPosition += 4;
                pdf.setFont('courier', 'bold');
                pdf.text('RAVON RESTAURANT', pageWidth / 2, yPosition, {
                    align: 'center'
                });
                yPosition += 4;
                pdf.setFont('courier', 'normal');
                pdf.text('Come again!', pageWidth / 2, yPosition, {
                    align: 'center'
                });
                yPosition += 8;
                pdf.setFontSize(6);
                pdf.text('System by SKM Labs', pageWidth / 2, yPosition, {
                    align: 'center'
                });

                // Create hidden iframe for printing instead of new window
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.style.position = 'absolute';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = 'none';
                document.body.appendChild(iframe);

                const pdfBlob = pdf.output('blob');
                const blobUrl = URL.createObjectURL(pdfBlob);
                iframe.src = blobUrl;

                iframe.onload = function() {
                    try {
                        const win = iframe.contentWindow;

                        // Cleanup function to remove iframe and revoke blob URL
                        const cleanup = () => {
                            try {
                                if (iframe && iframe.parentNode) {
                                    document.body.removeChild(iframe);
                                }
                            } catch (e) {
                                console.warn('Error removing print iframe:', e);
                            }
                            try {
                                URL.revokeObjectURL(blobUrl);
                            } catch (e) {
                                /* ignore */
                            }
                        };

                        // Prefer afterprint event to know when printing completed
                        const onAfterPrint = () => {
                            cleanup();
                            try {
                                win.removeEventListener('afterprint', onAfterPrint);
                            } catch (e) {}
                        };

                        // Attach listener if supported
                        try {
                            win.addEventListener('afterprint', onAfterPrint);
                        } catch (e) {
                            // ignore if can't attach
                        }

                        // Trigger print and focus the iframe window
                        setTimeout(() => {
                            try {
                                win.focus();
                            } catch (e) {}
                            try {
                                win.print();
                            } catch (e) {
                                console.error('Print failed:', e);
                            }
                        }, 300);

                        // Fallback: if afterprint doesn't fire, remove iframe after a safe delay
                        setTimeout(() => {
                            if (document.body.contains(iframe)) {
                                cleanup();
                            }
                        }, 8000);

                    } catch (err) {
                        console.error('Print iframe error', err);
                        // Best-effort cleanup
                        setTimeout(() => {
                            try {
                                if (iframe && iframe.parentNode) document.body.removeChild(iframe);
                            } catch (e) {}
                            try {
                                URL.revokeObjectURL(blobUrl);
                            } catch (e) {}
                        }, 3000);
                    }
                };

            } catch (error) {
                console.error('PDF Error:', error);
                alert('Failed to generate PDF: ' + error.message);
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
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
                selectedPaymentMethod = null; // Reset to no selection
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

            // Update time every second
            setInterval(updateTime, 1000);
            updateTime();

            // Add event listener for modal payment amount input
            const modalPaymentInput = document.getElementById('modal-payment-amount');
            if (modalPaymentInput) {
                modalPaymentInput.addEventListener('input', function() {
                    modalCustomerPayment = parseFloat(this.value) || 0;
                    updateModalTotals();
                });
            }

            // Force initial update of cart display and totals
            updateCartDisplay();
            updateTotals();
        });
    </script>
</body>

</html>