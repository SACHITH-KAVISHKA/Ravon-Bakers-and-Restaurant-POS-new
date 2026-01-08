<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-clock-history me-2"></i> Recent Invoices
            </h1>
            <p class="text-muted mb-0">Your last 5 transactions</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Receipt No</th>
                            <th>Date/Time</th>
                            <th>Payment Method</th>
                            <th>Total Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td>
                                <span class="badge bg-primary">{{ $sale->receipt_no }}</span>
                            </td>
                            <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span>
                            </td>
                            <td class="fw-bold">LKR {{ number_format($sale->total, 2) }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary view-items-btn me-1"
                                    data-sale-id="{{ $sale->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#saleItemsModal"
                                    title="View Details">
                                    <i class="bi bi-eye"></i> View
                                </button>

                                <button class="btn btn-sm btn-outline-success print-receipt-btn"
                                    data-receipt-url="{{ route('sales-report.receipt', ['sale' => $sale->id]) }}"
                                    title="Reprint Receipt">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-receipt display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No recent invoices found</h4>
                <p class="text-muted">You haven't issued any invoices yet.</p>
                <a href="{{ route('pos.index') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-calculator"></i> Go to POS
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="saleItemsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Receipt Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="saleDetailsLoading" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="saleDetailsContent" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Receipt No:</strong> <span id="modal-receipt-no"></span><br>
                                <strong>Date:</strong> <span id="modal-date"></span>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <strong>Total:</strong> LKR <span id="modal-total" class="fw-bold fs-5"></span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-items-list"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // View Items Logic
            $('.view-items-btn').on('click', function() {
                const saleId = $(this).data('sale-id');
                $('#saleDetailsLoading').show();
                $('#saleDetailsContent').hide();
                $('#modal-items-list').empty();

                // Note: Using the existing route from SalesReportController
                $.get(`{{ url('sales-report/sale-items') }}/${saleId}`)
                    .done(function(response) {
                        $('#modal-receipt-no').text(response.sale.receipt_no);
                        $('#modal-date').text(new Date(response.sale.created_at).toLocaleString());
                        $('#modal-total').text(parseFloat(response.sale.total).toFixed(2));

                        let itemsHtml = '';
                        response.items.forEach(function(item) {
                            itemsHtml += `
                                <tr>
                                    <td>${item.item_name}</td>
                                    <td class="text-center">${item.quantity}</td>
                                    <td class="text-end">${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td class="text-end">${parseFloat(item.total_price).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        $('#modal-items-list').html(itemsHtml);
                        $('#saleDetailsLoading').hide();
                        $('#saleDetailsContent').show();
                    });
            });

            // Print Logic
            $(document).on('click', '.print-receipt-btn', function() {
                const receiptUrl = $(this).data('receipt-url');
                let printFrame = document.getElementById('print-receipt-frame');
                if (!printFrame) {
                    printFrame = document.createElement('iframe');
                    printFrame.id = 'print-receipt-frame';
                    printFrame.style.display = 'none';
                    document.body.appendChild(printFrame);
                }
                printFrame.src = receiptUrl;
                printFrame.onload = function() {
                    printFrame.contentWindow.print();
                };
            });
        });
    </script>
    @endpush
</x-app-layout>
