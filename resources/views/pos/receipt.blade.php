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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
            <!-- Logo -->
            <div style="margin-bottom: 10px;">
                <img src="{{ asset('images/Bird.jpg') }}" alt="Logo" style="width: 60px; height: 60px; border-radius: 50%; display: block; margin: 0 auto;">
            </div>
            
            <div class="restaurant-name">RAVON BAKERS</div>
            <div style="font-size: 12px;">Restaurant & Bakery</div>
            
            @if($sale->branch)
                <!-- Branch Name -->
                <div style="font-size: 11px; font-weight: bold; margin-top: 5px;">
                    {{ $sale->branch->display_name ?? $sale->branch->name }}
                </div>
                
                <!-- Branch Address and Phone -->
                <div style="font-size: 10px; margin-top: 3px;">
                    @if($sale->branch->address)
                        <div>{{ $sale->branch->address }}</div>
                    @endif
                    @if($sale->branch->telephone)
                        <div>Tel: {{ $sale->branch->telephone }}</div>
                    @endif
                </div>
            @else
                <!-- Fallback if no branch data -->
                <div style="font-size: 10px; margin-top: 5px;">
                    <div>282/A 2, Kaduwela</div>
                    <div>Tel: 076 200 6007</div>
                </div>
            @endif
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
                    <div class="item-qty-price">{{ $item->quantity }} x LKR {{ number_format($item->unit_price, 2) }}</div>
                </div>
                <div>LKR {{ number_format($item->total_price, 2) }}</div>
            </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Sub Total:</span>
                <span>LKR {{ number_format($sale->subtotal, 2) }}</span>
            </div>

            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>LKR {{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        <div class="receipt-info" style="margin-top: 15px;">
            <div>
                <span>Payment Method:</span>
                <span>{{ strtoupper($sale->payment_method) }}</span>
            </div>

            {{-- Debug: Show actual database values --}}
            <div style="background: #f0f0f0; padding: 5px; margin: 5px 0; font-size: 8px; border: 1px solid #ccc;">
                Debug Info:<br>
                Payment Method DB: "{{ $sale->payment_method }}"<br>
                Customer Payment: {{ $sale->customer_payment ?? 'NULL' }}<br>
                Card Payment: {{ $sale->card_payment ?? 'NULL' }}<br>
                Balance: {{ $sale->balance ?? 'NULL' }}
            </div>

            @if($sale->payment_method === 'cash')
            <div>
                <span>Amount Paid:</span>
                <span>LKR {{ number_format($sale->customer_payment, 2) }}</span>
            </div>
            <div>
                <span>Balance:</span>
                <span>LKR {{ number_format($sale->balance, 2) }}</span>
            </div>
            @elseif(in_array($sale->payment_method, ['card_and_cash', 'CARD & CASH']) ||
            str_contains(strtolower($sale->payment_method), 'card') && str_contains(strtolower($sale->payment_method), 'cash'))
            <div>
                <span>Customer Payment:</span>
                <span>LKR {{ number_format($sale->customer_payment ?? 0, 2) }}</span>
            </div>
            <div>
                <span>Card Payment:</span>
                <span>LKR {{ number_format($sale->card_payment ?? 0, 2) }}</span>
            </div>
            <div>
                <span>Total Paid:</span>
                <span>LKR {{ number_format(($sale->customer_payment ?? 0) + ($sale->card_payment ?? 0), 2) }}</span>
            </div>
            @php
            $totalPaid = ($sale->customer_payment ?? 0) + ($sale->card_payment ?? 0);
            @endphp
            @if($totalPaid > $sale->total)
            <div>
                <span>Balance:</span>
                <span>LKR {{ number_format($totalPaid - $sale->total, 2) }}</span>
            </div>
            @elseif($totalPaid < $sale->total)
                <div>
                    <span>Credit Balance:</span>
                    <span>LKR {{ number_format($sale->total - $totalPaid, 2) }}</span>
                </div>
                @endif
                @elseif($sale->payment_method === 'card')
                <div>
                    <span>Card Payment:</span>
                    <span>LKR {{ number_format($sale->card_payment ?? 0, 2) }}</span>
                </div>
                @if(($sale->card_payment ?? 0) > $sale->total)
                <div>
                    <span>Balance:</span>
                    <span>LKR {{ number_format(($sale->card_payment ?? 0) - $sale->total, 2) }}</span>
                </div>
                @elseif(($sale->card_payment ?? 0) < $sale->total)
                    <div>
                        <span>Credit Balance:</span>
                        <span>LKR {{ number_format($sale->total - ($sale->card_payment ?? 0), 2) }}</span>
                    </div>
                    @endif
                    @elseif($sale->payment_method === 'credit')
                    <div>
                        <span>Amount Due:</span>
                        <span>LKR {{ number_format($sale->total, 2) }}</span>
                    </div>
                    <!-- For CREDIT payments, only show the amount due. Credit balance is intentionally omitted. -->
                    @else
                    {{-- Fallback for unrecognized payment methods --}}
                    <div style="color: red; font-size: 10px;">
                        Unrecognized payment method: "{{ $sale->payment_method }}"
                    </div>
                    @endif
        </div>

        <div class="footer">
            <div>Thank you for visiting</div>
            <div>
                <strong>
                    {{-- Use display_name if available, otherwise use branch name --}}
                    @if($sale->branch && $sale->branch->display_name)
                        {{ strtoupper($sale->branch->display_name) }}
                    @elseif($sale->branch)
                        {{ strtoupper($sale->branch->name) }}
                    @else
                        REVON BAKER
                    @endif
                </strong>
            </div>
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
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z" />
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
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
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
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
            </svg>
            New Order
        </a>
    </div>

    <style>
        /* Button hover effects */
        button:hover,
        a:hover {
            transform: translateY(-2px);
            filter: brightness(110%);
        }

        button:active,
        a:active {
            transform: translateY(0);
        }

        /* Hide buttons when printing */
        @media print {

            button,
            a {
                display: none !important;
            }
        }
    </style>

    <!-- Receipt Data for PDF -->
    <script type="application/json" id="receipt-data">
        {
            "receiptNo": "{{ $sale->receipt_no }}",
            "userName": "{{ $sale->user_name }}",
            "branchName": "{{ $sale->branch && $sale->branch->display_name ? strtoupper($sale->branch->display_name) : ($sale->branch ? strtoupper($sale->branch->name) : 'REVON BAKER') }}",
            "branchAddress": "{{ $sale->branch && $sale->branch->address ? $sale->branch->address : '282/A 2, Kaduwela' }}",
            "branchPhone": "{{ $sale->branch && $sale->branch->telephone ? $sale->branch->telephone : '076 200 6007' }}",
            "date": "{{ $sale->created_at->format('d/m/Y') }}",
            "time": "{{ $sale->created_at->format('H:i:s') }}",
            "subtotal": "{{ number_format($sale->subtotal, 2) }}",
            "total": "{{ number_format($sale->total, 2) }}",
            "paymentMethod": "{{ strtoupper($sale->payment_method) }}",
            "paymentMethodOriginal": "{{ $sale->payment_method }}",
            "customerPayment": "{{ number_format($sale->customer_payment ?? 0, 2) }}",
            "cardPayment": "{{ number_format($sale->card_payment ?? 0, 2) }}",
            "balance": "{{ number_format($sale->balance ?? 0, 2) }}",
            "creditBalance": "{{ number_format($sale->credit_balance ?? 0, 2) }}",
            "showCashDetails": {
                {
                    $sale - > payment_method === 'cash' ? 'true' : 'false'
                }
            },
            "showCardCashDetails": {
                {
                    (in_array($sale - > payment_method, ['card_and_cash', 'CARD & CASH']) || str_contains(strtolower($sale - > payment_method), 'card') && str_contains(strtolower($sale - > payment_method), 'cash')) ? 'true' : 'false'
                }
            },
            "showCardOnly": {
                {
                    $sale - > payment_method === 'card' ? 'true' : 'false'
                }
            },
            "showCredit": {
                {
                    $sale - > payment_method === 'credit' ? 'true' : 'false'
                }
            },
            "items": [
                @foreach($sale - > saleItems as $item) {
                    "name": "{{ addslashes($item->item_name) }}",
                    "quantity": {
                        {
                            $item - > quantity
                        }
                    },
                    "unitPrice": "{{ number_format($item->unit_price, 2) }}",
                    "totalPrice": "{{ number_format($item->total_price, 2) }}"
                }
                @if(!$loop - > last), @endif
                @endforeach
            ]
        }
    </script>

    <script>
        // Logo URL for PDF - encode logo image as base64 on server side
        @php
            $logoPath = public_path('images/logo.jpg');
            $logoBase64Data = '';
            if (file_exists($logoPath)) {
                $imageData = file_get_contents($logoPath);
                $logoBase64Data = 'data:image/jpeg;base64,' . base64_encode($imageData);
            }
        @endphp
        const logoUrl = "{!! asset('images/logo.jpg') !!}";
        let logoBase64 = @json($logoBase64Data); // Pre-loaded from server
        let circularLogoBase64 = null; // Will hold the circular version
        
        // Function to create circular logo from square image
        function createCircularLogo(base64Image, size) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = size;
                    canvas.height = size;
                    const ctx = canvas.getContext('2d');
                    
                    // Create circular clipping path
                    ctx.beginPath();
                    ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
                    ctx.closePath();
                    ctx.clip();
                    
                    // Draw image
                    ctx.drawImage(img, 0, 0, size, size);
                    
                    // Convert to base64
                    resolve(canvas.toDataURL('image/jpeg', 0.95));
                };
                img.onerror = reject;
                img.src = base64Image;
            });
        }
        
        console.log('Logo pre-loaded from server:', logoBase64 ? 'YES (length: ' + logoBase64.length + ')' : 'NO');
        
        // Function to convert image URL to base64
        function convertImageToBase64(url) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                // Remove crossOrigin for same-domain images
                
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = this.width;
                    canvas.height = this.height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(this, 0, 0);
                    
                    try {
                        const base64 = canvas.toDataURL('image/jpeg', 0.95);
                        resolve(base64);
                    } catch (e) {
                        console.error('❌ Canvas to base64 failed:', e);
                        reject(e);
                    }
                };
                
                img.onerror = function(e) {
                    console.error('❌ Image load failed:', e);
                    reject(e);
                };
                
                // Load image without timestamp first
                img.src = url;
            });
        }
        
        // Load logo on page load
        window.addEventListener('load', function() {
            convertImageToBase64(logoUrl)
                .then(base64 => {
                    logoBase64 = base64;
                })
                .catch(err => {
                    console.error('❌ Logo conversion failed:', err);
                });
        });

        // Function to download receipt as PDF
        async function downloadReceiptPDF() {
            // Create circular logo first
            if (logoBase64 && !circularLogoBase64) {
                try {
                    circularLogoBase64 = await createCircularLogo(logoBase64, 200);
                    console.log('Circular logo created for PDF');
                } catch (err) {
                    console.error('Error creating circular logo:', err);
                }
            }
            
            // Logo is already pre-loaded from server as base64
            console.log('PDF Generation - Logo status:', circularLogoBase64 ? 'Ready!' : 'Not available');
            
            const {
                jsPDF
            } = window.jspdf;

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
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [pageWidth, pageHeight]
            });

            // PDF generation code
            let yPosition = 10;

            // Add circular logo if available
            console.log('Logo status for PDF:', circularLogoBase64 ? 'Available' : 'Not available');
            if (circularLogoBase64) {
                try {
                    console.log('Adding circular logo to PDF...');
                    const logoSize = 16;
                    const logoX = pageWidth / 2 - logoSize / 2;
                    const logoY = yPosition;
                    
                    pdf.addImage(circularLogoBase64, 'JPEG', logoX, logoY, logoSize, logoSize);
                    yPosition += 20;
                    console.log('Circular logo added successfully!');
                } catch (e) {
                    console.error('Error adding logo to PDF:', e);
                    yPosition += 20;
                }
            } else {
                console.warn('No logo available - skipping logo');
                yPosition += 20;
            }

            // Header - Company Name (use branch name from data)
            pdf.setFontSize(14);
            pdf.setFont('courier', 'bold');
            // Print fixed business name in PDF header
            pdf.text('REVON BAKER', pageWidth / 2, yPosition, {
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
            pdf.text('Address: ' + receiptData.branchAddress, pageWidth / 2, yPosition, {
                align: 'center'
            });
            yPosition += 4;
            pdf.text('Phone: ' + receiptData.branchPhone, pageWidth / 2, yPosition, {
                align: 'center'
            });
            yPosition += 8;

            // Draw thick line
            pdf.setLineWidth(0.5);
            pdf.line(5, yPosition, pageWidth - 5, yPosition);
            yPosition += 6;

            // Receipt info section
            pdf.setFontSize(9);
            pdf.setFont('courier', 'normal');

            // Receipt details with proper spacing
            pdf.text('RECEIPT NO:', 5, yPosition);
            pdf.text(receiptData.receiptNo, pageWidth - 5, yPosition, {
                align: 'right'
            });
            yPosition += 5;

            pdf.text('USER:', 5, yPosition);
            pdf.text(receiptData.userName, pageWidth - 5, yPosition, {
                align: 'right'
            });
            yPosition += 5;

            pdf.text('DATE:', 5, yPosition);
            pdf.text(receiptData.date, pageWidth - 5, yPosition, {
                align: 'right'
            });
            yPosition += 5;

            pdf.text('TIME:', 5, yPosition);
            pdf.text(receiptData.time, pageWidth - 5, yPosition, {
                align: 'right'
            });
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
                    // compact header for continuation pages (circular logo)
                    if (circularLogoBase64) {
                        try {
                            const logoSize = 12;
                            const logoX = pageWidth / 2 - logoSize / 2;
                            const logoY = yPosition;
                            
                            pdf.addImage(circularLogoBase64, 'JPEG', logoX, logoY, logoSize, logoSize);
                            yPosition += 16;
                        } catch (e) {
                            console.error('Error adding logo on continuation page:', e);
                            yPosition += 16;
                        }
                    } else {
                        yPosition += 16;
                    }
                    pdf.setFontSize(12);
                    pdf.setFont('courier', 'bold');
                    // Continuation pages use fixed business name
                    pdf.text('REVON BAKER', pageWidth / 2, yPosition, {
                        align: 'center'
                    });
                    yPosition += 6;
                    pdf.setFontSize(8);
                    pdf.setFont('courier', 'normal');
                    pdf.text('Continued...', pageWidth / 2, yPosition, {
                        align: 'center'
                    });
                    yPosition += 6;
                    pdf.setLineWidth(0.5);
                    pdf.line(5, yPosition, pageWidth - 5, yPosition);
                    yPosition += 6;
                }

                const start = pageIndex * itemsPerPage;
                const end = Math.min(start + itemsPerPage, totalItems);

                for (let i = start; i < end; i++) {
                    const item = receiptData.items[i];
                    pdf.setFont('courier', 'bold');
                    pdf.text(item.name, 5, yPosition);
                    pdf.text(`LKR ${item.totalPrice}`, pageWidth - 5, yPosition, {
                        align: 'right'
                    });
                    yPosition += 4;

                    pdf.setFont('courier', 'normal');
                    pdf.text(`${item.quantity} x LKR ${item.unitPrice}`, 5, yPosition);
                    yPosition += 6;
                }

                // If last page, render totals and footer
                if (pageIndex === totalPages - 1) {
                    pdf.setLineDashPattern([1, 1], 0);
                    pdf.line(5, yPosition, pageWidth - 5, yPosition);
                    pdf.setLineDashPattern([], 0);
                    yPosition += 6;

                    pdf.setFont('courier', 'normal');
                    pdf.text('Sub Total:', 5, yPosition);
                    pdf.text(`LKR ${receiptData.subtotal}`, pageWidth - 5, yPosition, {
                        align: 'right'
                    });
                    yPosition += 6;

                    pdf.setFont('courier', 'bold');
                    pdf.setFontSize(11);
                    pdf.text('TOTAL:', 5, yPosition);
                    pdf.text(`LKR ${receiptData.total}`, pageWidth - 5, yPosition, {
                        align: 'right'
                    });
                    yPosition += 8;

                    pdf.setFontSize(9);
                    pdf.setFont('courier', 'normal');
                    pdf.text('Payment Method:', 5, yPosition);
                    pdf.text(receiptData.paymentMethod, pageWidth - 5, yPosition, {
                        align: 'right'
                    });
                    yPosition += 6;

                    if (receiptData.showCashDetails) {
                        // CASH payment method
                        pdf.text('Amount Paid:', 5, yPosition);
                        pdf.text(`LKR ${receiptData.customerPayment}`, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;
                        pdf.text('Balance:', 5, yPosition);
                        pdf.text(`LKR ${receiptData.balance}`, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 6;
                    } else if (receiptData.showCardCashDetails ||
                        receiptData.paymentMethodOriginal.includes('card_and_cash') ||
                        receiptData.paymentMethodOriginal.includes('CARD & CASH')) {
                        // CARD & CASH payment method
                        pdf.text('Customer Payment:', 5, yPosition);
                        pdf.text(`LKR ${receiptData.customerPayment}`, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;
                        pdf.text('Card Payment:', 5, yPosition);
                        pdf.text(`LKR ${receiptData.cardPayment}`, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;
                        // Remove commas from formatted numbers before parsing
                        const cashAmt = parseFloat(receiptData.customerPayment.replace(/,/g, '')) || 0;
                        const cardAmt = parseFloat(receiptData.cardPayment.replace(/,/g, '')) || 0;
                        const totalPaid = (cashAmt + cardAmt).toFixed(2);
                        pdf.text('Total Paid:', 5, yPosition);
                        pdf.text(`LKR ${totalPaid}`, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;

                        // Show balance only when overpaid, or credit balance when underpaid
                        const totalAmount = parseFloat(receiptData.total.replace(/,/g, '')) || 0;
                        if (parseFloat(totalPaid) > totalAmount) {
                            const balance = (parseFloat(totalPaid) - totalAmount).toFixed(2);
                            pdf.text('Balance:', 5, yPosition);
                            pdf.text(`LKR ${balance}`, pageWidth - 5, yPosition, {
                                align: 'right'
                            });
                            yPosition += 5;
                        } else if (parseFloat(totalPaid) < totalAmount) {
                            const creditBalance = (totalAmount - parseFloat(totalPaid)).toFixed(2);
                            pdf.text('Credit Balance:', 5, yPosition);
                            pdf.text(`LKR ${creditBalance}`, pageWidth - 5, yPosition, {
                                align: 'right'
                            });
                            yPosition += 5;
                        }
                        yPosition += 1;
                    } else if (receiptData.showCardOnly) {
                        // CARD only payment method
                        pdf.text('Card Payment:', 5, yPosition);
                        pdf.text(`LKR ${receiptData.cardPayment}`, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 5;

                        // Show balance only when overpaid, or credit balance when underpaid
                        const cardAmount = parseFloat(receiptData.cardPayment.replace(/,/g, '')) || 0;
                        const totalAmount = parseFloat(receiptData.total.replace(/,/g, '')) || 0;

                        if (cardAmount > totalAmount) {
                            const balance = (cardAmount - totalAmount).toFixed(2);
                            pdf.text('Balance:', 5, yPosition);
                            pdf.text(`LKR ${balance}`, pageWidth - 5, yPosition, {
                                align: 'right'
                            });
                            yPosition += 5;
                        } else if (cardAmount < totalAmount) {
                            const creditBalance = (totalAmount - cardAmount).toFixed(2);
                            pdf.text('Credit Balance:', 5, yPosition);
                            pdf.text(`LKR ${creditBalance}`, pageWidth - 5, yPosition, {
                                align: 'right'
                            });
                            yPosition += 5;
                        }
                        yPosition += 1;
                    } else if (receiptData.showCredit) {
                        // CREDIT payment method - show only the amount due, omit credit balance on printed receipt
                        pdf.text('Amount Due:', 5, yPosition);
                        pdf.text(`LKR ${receiptData.total}`, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 6;
                    } else {
                        // Debug: Unknown payment method
                        pdf.text('Payment Method Unknown:', 5, yPosition);
                        pdf.text(receiptData.paymentMethodOriginal, pageWidth - 5, yPosition, {
                            align: 'right'
                        });
                        yPosition += 6;
                    }

                    pdf.setLineDashPattern([1, 1], 0);
                    pdf.line(5, yPosition, pageWidth - 5, yPosition);
                    pdf.setLineDashPattern([], 0);
                    yPosition += 8;

                    pdf.setFontSize(8);
                    pdf.text('Thank you for visiting', pageWidth / 2, yPosition, {
                        align: 'center'
                    });
                    yPosition += 4;
                    pdf.setFont('courier', 'bold');
                    // Footer prints fixed business name
                    pdf.text('REVON BAKER', pageWidth / 2, yPosition, {
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
                    console.error('Error clearing session:', error);
                    // Still navigate even if there's an error
                    window.location.href = '{{ route("pos.index") }}?clear=1';
                });
        }
    </script>

    @if(request('return_to_pos'))
    <script>
        // Auto redirect after 3 seconds if coming from POS
        setTimeout(function() {
            if (confirm('Start a new order?')) {
                window.location.href = '{{ route("pos.index") }}';
            }
        }, 3000);
    </script>
    @endif
</body>

</html>