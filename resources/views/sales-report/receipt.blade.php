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

        /* Hide everything on screen when auto_print is enabled */
        body.auto-print-mode {
            visibility: hidden;
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
            border-bottom: none;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .restaurant-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-info {
            font-size: 10px;
            margin-bottom: 8px;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
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

        .item-amount {
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
                visibility: visible !important;
            }

            .receipt {
                box-shadow: none;
                border: none;
                margin: 0;
                visibility: visible !important;
            }

            .print-btn,
            .action-buttons {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="header">
            <!-- Logo -->
            <div style="margin-bottom: 10px;">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="width: 60px; height: 60px; border-radius: 50%; display: block; margin: 0 auto;">
            </div>

            @if($sale->branch)
                <!-- Branch Name -->
                <div class="restaurant-name">{{ $sale->branch->display_name ?? $sale->branch->name }}</div>

                <!-- Company Name (if exists) -->
                @if($sale->branch->company_name)
                    <div style="font-size: 12px; margin-top: 3px;">{{ $sale->branch->company_name }}</div>
                @endif

                <!-- Branch Address and Phone -->
                <div style="font-size: 10px; margin-top: 3px;">
                    @if($sale->branch->address)
                        <div>{{ $sale->branch->address }}</div>
                    @endif
                    @if($sale->branch->telephone)
                        <div>Tel: {{ $sale->branch->telephone }}</div>
                    @endif
                    @if($sale->branch->vat_reg_no)
                        <div>VAT Reg No: {{ $sale->branch->vat_reg_no }}</div>
                    @endif
                </div>
                <div style="font-size: 12px; font-weight: bold; margin-top: 6px;">INVOICE</div>`
            @else
                <!-- Fallback if no branch data -->
                <div style="font-size: 10px; margin-top: 5px;">
                    <div>282/A 2, Kaduwela</div>
                    <div>Tel: 076 200 6007</div>
                    <div>VAT Reg No: 103803284-7000</div>
                </div>
            @endif
        </div>

        <div class="receipt-info">
            <div>
                <span>CUSTOMER:</span>
                <span>{{ $sale->customer_name ?? 'Cash Customer' }}</span>
            </div>
            <div>
                <span>CUSTOMER VAT:</span>
                <span>{{ $sale->customer_vat ?? 'Not eligible for VAT' }}</span>
            </div>
            <div>
                <span>INVOICE NO:</span>
                <span>{{ $sale->receipt_no }}</span>
            </div>
            <div>
                <span>USER:</span>
                <span>{{ $sale->user_name ?? 'N/A' }}</span>
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
            @php
                // Calculate the combined tax factor to derive base prices
                $taxFactor = (1 + ($ssclRate / 100)) * (1 + ($vatRate / 100));
            @endphp

            @foreach($sale->saleItems as $item)
            @php
                // Without tax factor, we can derive the base unit price and line total for each item
                $itemBaseUnitPrice = $item->unit_price / $taxFactor;
                $itemBaseLineTotal = $itemBaseUnitPrice * $item->quantity;
            @endphp
            <div class="item">
                <div class="item-details">
                    <div class="item-name">{{ $item->item_name }}</div>
                    <div class="item-qty-price">{{ $item->quantity }} x LKR {{ number_format($itemBaseUnitPrice, 2) }}</div>
                </div>
                <div class="item-amount">LKR {{ number_format($itemBaseLineTotal, 2) }}</div>
            </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Sub Total (Base):</span>
                <span>LKR {{ number_format($sale->subtotal, 2) }}</span>
            </div>

            <div class="total-row" style="font-size: 11px; color: #555;">
            <span>SSCL ({{ $ssclRate }}%):</span>
            <span>LKR {{ number_format($sale->sscl_amount, 2) }}</span>
            </div>

            <div class="total-row" style="font-size: 11px; color: #555;">
                <span>VAT ({{ $vatRate }}%):</span>
                <span>LKR {{ number_format($sale->vat_amount, 2) }}</span>
            </div>

            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>LKR {{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        <div class="receipt-info" style="margin-top: 15px; border-bottom: none;">
            <div>
                <span>Payment Method:</span>
                <span>{{ strtoupper($sale->payment_method) }}</span>
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
                <div>System by Jayawardena Group</div>
            </div>
        </div>
    </div>

    <div class="action-buttons" style="text-align: center; margin: 30px 0; display: flex; gap: 15px; justify-content: center;">
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
        <button type="button" onclick="window.close()" style="
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
            </svg>
            Close
        </button>
    </div>

    <style>
        /* Button hover effects */
        button:hover {
            transform: translateY(-2px);
            filter: brightness(110%);
        }

        button:active {
            transform: translateY(0);
        }
    </style>

    <script>
        // Check if loaded in iframe (for direct printing from sales report)
        if (window.self !== window.top) {
            // Loaded in iframe - hide buttons
            document.addEventListener('DOMContentLoaded', function() {
                const actionButtons = document.querySelector('.action-buttons');
                if (actionButtons) {
                    actionButtons.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>
