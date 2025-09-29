<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-bar-chart-fill me-2"></i> Daily Sales Report
        </h1>
        <div class="text-muted">
            <i class="bi bi-calendar3"></i>
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales-report.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Receipt No, User Name, Payment Method..." 
                           value="{{ $searchTerm }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total Transactions</div>
                            <div class="h4">{{ $totals->total_transactions ?? 0 }}</div>
                        </div>
                        <i class="bi bi-receipt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total Subtotal</div>
                            <div class="h4">Rs. {{ number_format($totals->total_subtotal ?? 0, 2) }}</div>
                        </div>
                        <i class="bi bi-cash-coin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Customer Payment</div>
                            <div class="h4">Rs. {{ number_format($totals->total_customer_payment ?? 0, 2) }}</div>
                        </div>
                        <i class="bi bi-credit-card fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">Total Balance</div>
                            <div class="h4">Rs. {{ number_format($totals->total_balance ?? 0, 2) }}</div>
                        </div>
                        <i class="bi bi-wallet2 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Sales Transactions</h5>
        <a href="{{ route('sales-report.export', request()->query()) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </a>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-body">
            @if($sales->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Receipt No</th>
                                <th>User Name</th>
                                <th>Subtotal</th>
                                <th>Payment Method</th>
                                <th>Customer Payment</th>
                                <th>Balance</th>
                                <th>Date/Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $sale->receipt_no }}</span>
                                    </td>
                                    <td>{{ $sale->user_name }}</td>
                                    <td>Rs. {{ number_format($sale->subtotal, 2) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $sale->payment_method }}</span>
                                    </td>
                                    <td>Rs. {{ number_format($sale->customer_payment, 2) }}</td>
                                    <td class="{{ $sale->balance > 0 ? 'text-success' : ($sale->balance < 0 ? 'text-danger' : '') }}">
                                        Rs. {{ number_format($sale->balance, 2) }}
                                    </td>
                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-items-btn" 
                                                data-sale-id="{{ $sale->id }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#saleItemsModal">
                                            <i class="bi bi-eye"></i> View Items
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $sales->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No sales found</h4>
                    <p class="text-muted">Try adjusting your search criteria or date range.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sale Items Modal -->
    <div class="modal fade" id="saleItemsModal" tabindex="-1" aria-labelledby="saleItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleItemsModalLabel">Sale Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="saleDetailsLoading" class="text-center py-3">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="saleDetailsContent" style="display: none;">
                        <!-- Sale Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Sale Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Receipt No:</strong> <span id="modal-receipt-no"></span><br>
                                        <strong>User:</strong> <span id="modal-user-name"></span><br>
                                        <strong>Payment Method:</strong> <span id="modal-payment-method"></span><br>
                                        <strong>Date:</strong> <span id="modal-date"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Subtotal:</strong> Rs. <span id="modal-subtotal"></span><br>
                                        <strong>Discount:</strong> Rs. <span id="modal-discount"></span><br>
                                        <strong>Tax:</strong> Rs. <span id="modal-tax"></span><br>
                                        <strong>Total:</strong> Rs. <span id="modal-total"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items List -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Items Purchased</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-items-list">
                                            <!-- Items will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
            $('.view-items-btn').on('click', function() {
                const saleId = $(this).data('sale-id');
                $('#saleDetailsLoading').show();
                $('#saleDetailsContent').hide();
                
                // Clear previous data
                $('#modal-items-list').empty();
                
                // Fetch sale details
                $.get(`{{ url('sales-report/sale-items') }}/${saleId}`)
                    .done(function(response) {
                        // Populate sale information
                        $('#modal-receipt-no').text(response.sale.receipt_no);
                        $('#modal-user-name').text(response.sale.user_name);
                        $('#modal-payment-method').text(response.sale.payment_method);
                        $('#modal-date').text(new Date(response.sale.created_at).toLocaleString());
                        $('#modal-subtotal').text(parseFloat(response.sale.subtotal).toFixed(2));
                        $('#modal-discount').text(parseFloat(response.sale.discount).toFixed(2));
                        $('#modal-tax').text(parseFloat(response.sale.tax).toFixed(2));
                        $('#modal-total').text(parseFloat(response.sale.total).toFixed(2));
                        
                        // Populate items
                        let itemsHtml = '';
                        response.items.forEach(function(item) {
                            itemsHtml += `
                                <tr>
                                    <td>${item.item_name}</td>
                                    <td>${item.quantity}</td>
                                    <td>Rs. ${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td>Rs. ${parseFloat(item.total_price).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        $('#modal-items-list').html(itemsHtml);
                        
                        $('#saleDetailsLoading').hide();
                        $('#saleDetailsContent').show();
                    })
                    .fail(function() {
                        $('#saleDetailsLoading').hide();
                        $('#modal-items-list').html('<tr><td colspan="4" class="text-center text-danger">Error loading sale details</td></tr>');
                        $('#saleDetailsContent').show();
                    });
            });
        });
    </script>
    @endpush
</x-app-layout>