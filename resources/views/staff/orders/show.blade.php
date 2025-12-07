@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="fw-bold text-dark">Order Details: #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('staff.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Orders
                </a>
                <button onclick="printReceipt()" class="btn btn-success text-white">
                    <i class="bi bi-printer"></i> Print Order Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0"><i class="bi bi-basket"></i> Ordered Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Item Name</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td class="ps-4">
                                    {{ $item->item->item_name }} <br>
                                    <small class="text-muted">{{ $item->item->item_code }}</small>
                                </td>
                                <td class="text-end">Rs. {{ number_format($item->price, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end pe-4 fw-bold">Rs. {{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold pt-3">Total Amount:</td>
                                <td class="text-end pe-4 fw-bold pt-3 fs-5">Rs. {{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 border-bottom pb-2">
                    <label class="text-muted small">Customer Name</label>
                    <div class="fw-bold fs-5">{{ $order->customer_name }}</div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="text-muted small">Branch</label>
                    <div class="fw-bold">{{ $order->branch->name }}</div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="text-muted small">Date & Time</label>
                    <div class="fw-bold">{{ $order->date_time->format('Y-M-d h:i A') }}</div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Staff Member</label>
                    <div>{{ $order->user->name }}</div>
                </div>

                @if($order->notes)
                <div class="alert alert-secondary mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> <strong>Note:</strong> {{ $order->notes }}
                </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Payment Method:</span>
                    <span class="fw-bold text-uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Paid Amount:</span>
                    <span class="fw-bold text-success">Rs. {{ number_format($order->paid_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pt-2 border-top">
                    <span class="fw-bold">Balance Due:</span>
                    <span class="fw-bold text-danger fs-5">Rs. {{ number_format($order->balance_amount, 2) }}</span>
                </div>

                <div class="mt-3 text-center">
                    @if($order->status == 2)
                        <span class="badge bg-success w-100 py-2">PAYMENT COMPLETE</span>
                    @else
                        <span class="badge bg-warning text-dark w-100 py-2">PARTIAL / UNPAID</span>
                    @endif
                </div>
            </div>
        </div>
    </div> --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 border-bottom pb-2">
                    <label class="text-muted small">Customer Name</label>
                    <div class="fw-bold fs-5">{{ $order->customer_name }}</div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="text-muted small">Branch</label>
                    <div class="fw-bold">{{ $order->branch->name }}</div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="text-muted small">Placed On</label>
                    <div class="fw-bold">
                        {{ $order->created_at->format('Y-M-d') }}
                        <span class="text-muted small">({{ $order->created_at->format('h:i A') }})</span>
                    </div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="text-muted small text-primary fw-bold">Collect Date & Time</label>
                    <div class="fw-bold text-dark">
                        {{ $order->date_time->format('Y-M-d') }}
                        <span class="text-muted small">({{ $order->date_time->format('h:i A') }})</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Staff Member</label>
                    <div>{{ $order->user->name }}</div>
                </div>

                @if($order->notes)
                <div class="alert alert-secondary mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> <strong>Note:</strong> {{ $order->notes }}
                </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Payment Method:</span>
                    <span class="fw-bold text-uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Paid Amount:</span>
                    <span class="fw-bold text-success">Rs. {{ number_format($order->paid_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pt-2 border-top">
                    <span class="fw-bold">Balance Due:</span>
                    <span class="fw-bold text-danger fs-5">Rs. {{ number_format($order->balance_amount, 2) }}</span>
                </div>

                <div class="mt-3 text-center">
                    @if($order->status == 2)
                        <span class="badge bg-success w-100 py-2">PAYMENT COMPLETE</span>
                    @else
                        <span class="badge bg-warning text-dark w-100 py-2">PARTIAL / UNPAID</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Prepare Order Data from PHP to JS
    const orderData = {
        // Updated Label: Order No
        orderNo: "#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}",
        userName: "{{ $order->user->name }}",
        date: "{{ $order->created_at->format('d/m/Y') }}",
        time: "{{ $order->created_at->format('h:i:A') }}",
        collectDate: "{{ $order->date_time->format('d/m/Y h:i A') }}",
        customerName: "{{ $order->customer_name }}",
        items: [
            @foreach($order->items as $item)
            {
                name: "{{ $item->item->item_name }}",
                quantity: {{ $item->quantity }},
                unitPrice: {{ $item->price }},
                totalPrice: {{ $item->subtotal }}
            },
            @endforeach
        ],
        subtotal: "{{ number_format($order->total_amount, 2, '.', '') }}",
        total: "{{ number_format($order->total_amount, 2, '.', '') }}",
        paidAmount: "{{ number_format($order->paid_amount, 2, '.', '') }}",
        balance: "{{ number_format($order->balance_amount, 2, '.', '') }}",
        paymentMethod: "{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}",

        branchName: "{{ $order->branch->display_name ?? $order->branch->name }}",
        // New: Company Name
        companyName: "{{ $order->branch->company_name ?? '' }}",
        branchAddress: "{{ $order->branch->address ?? '' }}",
        branchPhone: "{{ $order->branch->telephone ?? '' }}"
    };

    // 2. QZ Tray Configuration (Certificate & Signature)
    qz.security.setCertificatePromise(function(resolve, reject) {
        resolve(`-----BEGIN CERTIFICATE-----
            MIIDozCCAougAwIBAgIUWJpvpJOkleU6lWsqrMKfsq9u6OowDQYJKoZIhvcNAQEL
            BQAwYTELMAkGA1UEBhMCTEsxEDAOBgNVBAgMB1dlc3Rlcm4xEDAOBgNVBAcMB0Nv
            bG9tYm8xFTATBgNVBAoMDFJhdm9uIEJha2VyczEXMBUGA1UEAwwOMTI3LjAuMC4x
            OjgwMDAwHhcNMjUxMTE3MTgwNzI0WhcNMzUxMTE1MTgwNzI0WjBhMQswCQYDVQQG
            EwJMSzEQMA4GA1UECAwHV2VzdGVybjEQMA4GA1UEBwwHQ29sb21ibzEVMBMGA1UE
            CgwMUmF2b24gQmFrZXJzMRcwFQYDVQQDDA4xMjcuMC4wLjE6ODAwMDCCASIwDQYJ
            KoZIhvcNAQEBBQADggEPADCCAQoCggEBANF0JduabBoiZ1M7R28FmCmvUEDYy+2z
            uz+zQZiBGT3pm3gD2HgZfvhooGywwX2lmEn5Q5wvq3dodcqpd+Nr7xDE6U2QEcGS
            UEi0aDbTCBY2VIRP5HNP33hDqNOq06akEtJRxGQ43hOLxoSWZjYxe7hIstVfp2fU
            4j+uycPv9E8Cxo6eIM6NCFfRN1mIbkIIjgVfAmOaJb1y+TbD8z5NxXAfPf31GvXi
            7AJ3gnr6khs6XyW5umcesBeOijBL+lUyTRU26GQWiduoaeoTToN9UkX3ZEvfPlR7
            YLYqfRHnT4RJxRs+BcTDMsy0JHI5MGD/Ur/u8uXNgK2mqrfPLado9y0CAwEAAaNT
            MFEwHQYDVR0OBBYEFMSl/4RhhGD0mRYBD2bH4n+t/cNBMB8GA1UdIwQYMBaAFMSl
            /4RhhGD0mRYBD2bH4n+t/cNBMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQEL
            BQADggEBADlwDYAu7LGzj+pGROVavOeVczrb8RibbIbXrIViV31iKC1uwXRmtTY1
            amAX+oEfMry3TIy//BHsJzGkAd6ozfosez33G4bbN8/y1Q9ZvcuaaHPT4DIBYrdR
            GX/B6TtAm63VxXyjfwrV4OUbbqwdgMtKuviRprB9A+oCE1QPa74p33hgy8UHYOCK
            g9lFgnRkyrLOb4fh2SmtjHhRV4aZf5CM+UbqBQAMiiuhHLAbqbmhBP3BYzVVZ066
            9moVkpDvvNADqW3FH6epeBDL8RyQXj2yikCyD3xXJIAih815xLJMh/pOmuqEjHdd
            NESCtDma6uLcth74mGaBwU3G3KsOCP4=
        -----END CERTIFICATE-----`);
    });

    qz.security.setSignaturePromise(function(toSign) {
        return function(resolve, reject) {
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            var token = tokenMeta ? tokenMeta.content : "";
            fetch('/qz/sign', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ data: toSign })
            })
            .then(response => response.json())
            .then(data => data.signature ? resolve(data.signature) : reject(data.error))
            .catch(err => reject(err));
        };
    });

    // 3. Print Function
    async function printReceipt() {
        try {
            if (!qz.websocket.isActive()) {
                await qz.websocket.connect();
            }

            const printerName = "XP-80C";

            let config;
            try {
                 config = qz.configs.create(printerName);
            } catch(e) {
                 config = qz.configs.create(await qz.printers.getDefault());
            }

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [80, 297]
            });

            let yPosition = 10;
            const pageWidth = 80;
            const leftMargin = 5;
            const rightMargin = 5;

            // --- HEADER ---
            pdf.setFont('courier', 'bold');
            pdf.setFontSize(14);
            pdf.text(orderData.branchName, pageWidth / 2, yPosition, { align: 'center' });
            yPosition += 5;

            // **UPDATED: Show Company Name if exists**
            if(orderData.companyName && orderData.companyName.trim() !== "") {
                pdf.setFontSize(10);
                pdf.setFont('courier', 'normal');
                pdf.text(orderData.companyName, pageWidth / 2, yPosition, { align: 'center' });
                yPosition += 5;
            }

            pdf.setFontSize(9);
            pdf.setFont('courier', 'normal');
            if(orderData.branchAddress) {
                pdf.text(orderData.branchAddress, pageWidth / 2, yPosition, { align: 'center' });
                yPosition += 4;
            }
            if(orderData.branchPhone) {
                pdf.text('Tel: ' + orderData.branchPhone, pageWidth / 2, yPosition, { align: 'center' });
                yPosition += 6;
            }

            pdf.setLineWidth(0.5);
            pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
            yPosition += 6;

            // **UPDATED: Receipt Title**
            pdf.setFont('courier', 'bold');
            pdf.setFontSize(12);
            pdf.text("ORDER RECEIPT", pageWidth / 2, yPosition, { align: 'center' });
            yPosition += 8;

            // --- ORDER INFO ---
            pdf.setFontSize(9);
            pdf.setFont('courier', 'normal');

            // **UPDATED: Changed Label to ORDER NO**
            pdf.text('ORDER NO:', leftMargin, yPosition);
            pdf.text(orderData.orderNo, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 5;

            pdf.text('DATE:', leftMargin, yPosition);
            pdf.text(orderData.date + ' ' + orderData.time, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 5;

            pdf.text('COLLECT DATE:', leftMargin, yPosition);
            pdf.text(orderData.collectDate, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 5;

            pdf.text('CUSTOMER:', leftMargin, yPosition);
            pdf.text(orderData.customerName, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 5;

            pdf.text('USER:', leftMargin, yPosition);
            pdf.text(orderData.userName, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 8;

            // --- ITEMS HEADER ---
            pdf.setLineWidth(0.3);
            pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
            yPosition += 5;

            // --- ITEMS LIST ---
            orderData.items.forEach(item => {
                pdf.setFont('courier', 'bold');
                pdf.text(item.name, leftMargin, yPosition);

                pdf.text('LKR ' + item.totalPrice.toFixed(2), pageWidth - rightMargin, yPosition, { align: 'right' });
                yPosition += 4;

                pdf.setFont('courier', 'normal');
                pdf.setFontSize(8);
                pdf.text(`${item.quantity} x LKR ${item.unitPrice.toFixed(2)}`, leftMargin + 2, yPosition);
                yPosition += 6;
            });

            // --- TOTALS ---
            yPosition += 2;
            pdf.setLineDashPattern([1, 1], 0);
            pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
            pdf.setLineDashPattern([], 0);
            yPosition += 6;

            pdf.setFont('courier', 'bold');
            pdf.setFontSize(11);
            pdf.text('TOTAL:', leftMargin, yPosition);
            pdf.text(`LKR ${orderData.total}`, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 8;

            // --- PAYMENT DETAILS ---
            pdf.setFont('courier', 'normal');
            pdf.setFontSize(9);
            pdf.text('Payment Method:', leftMargin, yPosition);
            pdf.text(orderData.paymentMethod, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 5;

            pdf.text('Paid Amount:', leftMargin, yPosition);
            pdf.text(`LKR ${orderData.paidAmount}`, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 5;

            const balanceVal = parseFloat(orderData.balance);
            const label = balanceVal >= 0 ? 'Balance:' : 'Credit Balance:';
            const displayBal = Math.abs(balanceVal).toFixed(2);

            pdf.text(label, leftMargin, yPosition);
            pdf.text(`LKR ${displayBal}`, pageWidth - rightMargin, yPosition, { align: 'right' });
            yPosition += 5;

            // --- FOOTER ---
            pdf.setLineDashPattern([1, 1], 0);
            pdf.line(leftMargin, yPosition, pageWidth - rightMargin, yPosition);
            yPosition += 6;

            pdf.setFontSize(8);
            pdf.text('Thank you for visiting!', pageWidth / 2, yPosition, { align: 'center' });

            const pdfBase64 = pdf.output('datauristring').split(',')[1];
            const data = [{ type: 'pdf', format: 'base64', data: pdfBase64 }];
            await qz.print(config, data);

        } catch (err) {
            console.error(err);
            alert("Printing Failed: " + err.message);
        }
    }

    // Auto-Print if redirected from Create Order
    @if(session('print_receipt'))
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(printReceipt, 1000);
        });
    @endif
</script>
@endsection
