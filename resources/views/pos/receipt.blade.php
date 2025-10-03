<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->receipt_no }}</title>
    
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
        <button type="button" onclick="downloadReceiptPDF()" style="
            background: linear-gradient(135deg, #fd7e14 0%, #e55a1b 100%);
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
            box-shadow: 0 4px 15px rgba(253, 126, 20, 0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download PDF
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
    
    <!-- Receipt Data for PDF -->
    <script type="application/json" id="receipt-data">
        {
            "receiptNo": "{{ $sale->receipt_no }}",
            "userName": "{{ $sale->user_name }}",
            "date": "{{ $sale->created_at->format('d/m/Y') }}",
            "time": "{{ $sale->created_at->format('H:i:s') }}",
            "subtotal": "{{ number_format($sale->subtotal, 2) }}",
            "total": "{{ number_format($sale->total, 2) }}",
            "paymentMethod": "{{ $sale->payment_method }}",
            @if(in_array($sale->payment_method, ['CASH', 'CARD & CASH']) && $sale->customer_payment)
            "amountPaid": "{{ number_format($sale->customer_payment, 2) }}",
            "balance": "{{ number_format($sale->balance, 2) }}",
            "showCashDetails": true,
            @else
            "showCashDetails": false,
            @endif
            "items": [
                @foreach($sale->saleItems as $item)
                {
                    "name": "{{ addslashes($item->item_name) }}",
                    "quantity": {{ $item->quantity }},
                    "unitPrice": "{{ number_format($item->unit_price, 2) }}",
                    "totalPrice": "{{ number_format($item->total_price, 2) }}"
                }@if(!$loop->last),@endif
                @endforeach
            ]
        }
    </script>

    <script>

        // Function to download receipt as PDF
        function downloadReceiptPDF() {
            const { jsPDF } = window.jspdf;

            // Get receipt data
            const receiptData = JSON.parse(document.getElementById('receipt-data').textContent);

            // Compute dynamic height based on items
            const itemCount = receiptData.items ? receiptData.items.length : 0;
            const itemHeight = 6; // mm per item
            const headerFooterHeight = 80;
            const dynamicHeight = Math.max(200, headerFooterHeight + itemCount * itemHeight);

            // Use a fixed page size and paginate items across pages for reliable printing
            const pageWidth = 80;
            const pageHeight = 200; // mm
            const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: [pageWidth, pageHeight] });

            // PDF generation code
            let yPosition = 10;
            
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
            
            // Paginate items
            const approxItemHeight = 10; // title + qty line
            const headerSpace = yPosition; // used header height
            const footerReserve = 60; // reserve space for totals/footer on last page
            const itemsPerPage = Math.max(1, Math.floor((pageHeight - headerSpace - footerReserve) / approxItemHeight));
            const totalItems = receiptData.items.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
                if (pageIndex > 0) {
                    pdf.addPage();
                    yPosition = 10;
                    // compact header for continuation pages
                    pdf.setFontSize(10);
                    pdf.setFont('helvetica', 'bold');
                    pdf.circle(pageWidth/2, yPosition + 5, 8);
                    pdf.text('RB', pageWidth/2, yPosition + 7, { align: 'center' });
                    yPosition += 14;
                    pdf.setFontSize(12);
                    pdf.setFont('courier', 'bold');
                    pdf.text('RAVON BAKERS', pageWidth/2, yPosition, { align: 'center' });
                    yPosition += 6;
                    pdf.setFontSize(8);
                    pdf.setFont('courier', 'normal');
                    pdf.text('Continued...', pageWidth/2, yPosition, { align: 'center' });
                    yPosition += 6;
                    pdf.setLineWidth(0.5);
                    pdf.line(5, yPosition, pageWidth-5, yPosition);
                    yPosition += 6;
                }

                const start = pageIndex * itemsPerPage;
                const end = Math.min(start + itemsPerPage, totalItems);

                for (let i = start; i < end; i++) {
                    const item = receiptData.items[i];
                    pdf.setFont('courier', 'bold');
                    pdf.text(item.name, 5, yPosition);
                    pdf.text(`Rs. ${item.totalPrice}`, pageWidth-5, yPosition, { align: 'right' });
                    yPosition += 4;

                    pdf.setFont('courier', 'normal');
                    pdf.text(`${item.quantity} x Rs. ${item.unitPrice}`, 5, yPosition);
                    yPosition += 6;
                }

                // If last page, render totals and footer
                if (pageIndex === totalPages - 1) {
                    pdf.setLineDashPattern([1, 1], 0);
                    pdf.line(5, yPosition, pageWidth-5, yPosition);
                    pdf.setLineDashPattern([], 0);
                    yPosition += 6;

                    pdf.setFont('courier', 'normal');
                    pdf.text('Sub Total:', 5, yPosition);
                    pdf.text(`Rs. ${receiptData.subtotal}`, pageWidth-5, yPosition, { align: 'right' });
                    yPosition += 6;

                    pdf.setFont('courier', 'bold');
                    pdf.setFontSize(11);
                    pdf.text('TOTAL:', 5, yPosition);
                    pdf.text(`Rs. ${receiptData.total}`, pageWidth-5, yPosition, { align: 'right' });
                    yPosition += 8;

                    pdf.setFontSize(9);
                    pdf.setFont('courier', 'normal');
                    pdf.text('Payment Method:', 5, yPosition);
                    pdf.text(receiptData.paymentMethod, pageWidth-5, yPosition, { align: 'right' });
                    yPosition += 6;

                    if (receiptData.showCashDetails) {
                        pdf.text('Amount Paid:', 5, yPosition);
                        pdf.text(`Rs. ${receiptData.amountPaid}`, pageWidth-5, yPosition, { align: 'right' });
                        yPosition += 5;
                        pdf.text('Balance:', 5, yPosition);
                        pdf.text(`Rs. ${receiptData.balance}`, pageWidth-5, yPosition, { align: 'right' });
                        yPosition += 6;
                    }

                    pdf.setLineDashPattern([1, 1], 0);
                    pdf.line(5, yPosition, pageWidth-5, yPosition);
                    pdf.setLineDashPattern([], 0);
                    yPosition += 8;

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
                }
            }
            
            // Generate filename
            const filename = `Receipt_${receiptData.receiptNo}_{{ $sale->created_at->format("Y-m-d") }}.pdf`;
            
            // Download the PDF
            pdf.save(filename);
        }

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