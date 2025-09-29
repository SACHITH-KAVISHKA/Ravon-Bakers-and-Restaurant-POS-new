<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->receipt_no }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .restaurant-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-info {
            font-size: 12px;
            margin-bottom: 15px;
        }
        
        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .items {
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-qty-price {
            color: #666;
            font-size: 10px;
        }
        
        .totals {
            font-size: 12px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            border-top: 1px dashed #333;
            padding-top: 10px;
        }
        
        .print-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 20px auto;
            display: block;
        }
        
        .print-btn:hover {
            background: #218838;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt {
                box-shadow: none;
                border: none;
                margin: 0;
            }
            
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
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
                <span>{{ $sale->receipt_no }}</span>
            </div>
            <div>
                <span>TERMINAL:</span>
                <span>{{ $sale->terminal }}</span>
            </div>
            <div>
                <span>USER:</span>
                <span>{{ $sale->user_name }}</span>
            </div>
            <div>
                <span>DATE:</span>
                <span>{{ $sale->created_at->format('d/m/Y') }}</span>
            </div>
            <div>
                <span>TIME:</span>
                <span>{{ $sale->created_at->format('H:i:s') }}</span>
            </div>
        </div>
        
        <div class="items">
            @foreach($sale->saleItems as $item)
            <div class="item">
                <div class="item-details">
                    <div class="item-name">{{ $item->item_name }}</div>
                    <div class="item-qty-price">{{ $item->quantity }} x Rs. {{ number_format($item->unit_price, 2) }}</div>
                </div>
                <div>Rs. {{ number_format($item->total_price, 2) }}</div>
            </div>
            @endforeach
        </div>
        
        <div class="totals">
            <div class="total-row">
                <span>Sub Total:</span>
                <span>Rs. {{ number_format($sale->subtotal, 2) }}</span>
            </div>
            
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rs. {{ number_format($sale->total, 2) }}</span>
            </div>
        </div>
        
        <div class="receipt-info" style="margin-top: 15px;">
            <div>
                <span>Payment Method:</span>
                <span>{{ $sale->payment_method }}</span>
            </div>
            
            @if(in_array($sale->payment_method, ['CASH', 'CARD & CASH']) && $sale->customer_payment)
            <div>
                <span>Amount Paid:</span>
                <span>Rs. {{ number_format($sale->customer_payment, 2) }}</span>
            </div>
            <div>
                <span>Balance:</span>
                <span>Rs. {{ number_format($sale->balance, 2) }}</span>
            </div>
            @endif
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
    
    <div style="text-align: center; margin: 30px 0; display: flex; gap: 15px; justify-content: center;">
        <button type="button" onclick="window.print()" style="
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print Receipt
        </button>
        <a href="#" onclick="startNewOrder()" style="
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            New Order
        </a>
    </div>
    
    <style>
        /* Button hover effects */
        button:hover, a:hover {
            transform: translateY(-2px);
            filter: brightness(110%);
        }
        
        button:active, a:active {
            transform: translateY(0);
        }
        
        /* Hide buttons when printing */
        @media print {
            button, a {
                display: none !important;
            }
        }
    </style>
    
    <script>
        // Function to start a new order
        function startNewOrder() {
            // Show loading state
            event.target.innerHTML = '<span>Clearing...</span>';
            event.target.style.pointerEvents = 'none';
            
            // Clear server session data
            fetch('{{ route("pos.clear-session") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(response => response.json())
            .then(data => {
                // Clear browser storage
                if (typeof(Storage) !== "undefined") {
                    localStorage.removeItem('pos_cart');
                    localStorage.removeItem('pos_customer_payment');
                    localStorage.removeItem('pos_selected_payment_method');
                    localStorage.removeItem('pos_receipt_no');
                    sessionStorage.clear();
                }
                
                // Navigate to clean POS dashboard with clear parameter
                window.location.href = '{{ route("pos.index") }}?clear=1';
            }).catch((error) => {
                console.log('Error clearing session:', error);
                // Still navigate even if there's an error
                window.location.href = '{{ route("pos.index") }}?clear=1';
            });
        }
    </script>
    
    @if(request('return_to_pos'))
    <script>
        // Auto redirect after 3 seconds if coming from POS
        setTimeout(function() {
            if(confirm('Start a new order?')) {
                window.location.href = '{{ route("pos.index") }}';
            }
        }, 3000);
    </script>
    @endif
</body>
</html>