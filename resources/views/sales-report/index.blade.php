<x-app-layout>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-bar-chart-fill me-2"></i> Daily Sales Report
            </h1>
            <p class="text-muted mb-0 d-none d-md-block">View and export sales transactions</p>
        </div>
        <div class="text-muted">
            <i class="bi bi-calendar3"></i>
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales-report.index') }}" class="row g-3">
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="start_date" class="form-label fw-semibold">Start Date</label>
                    <input type="date" class="form-control form-control-lg" id="start_date" name="start_date"
                        value="{{ $startDate }}">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="end_date" class="form-label fw-semibold">End Date</label>
                    <input type="date" class="form-control form-control-lg" id="end_date" name="end_date"
                        value="{{ $endDate }}">
                </div>
                <div class="col-12 col-md-8 col-lg-4">
                    <label for="branch_id" class="form-label fw-semibold">Branch</label>
                    <select class="form-select form-select-lg" id="branch_id" name="branch_id">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            @continue(strtolower($branch->name) === 'main branch')
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards removed per admin view requirement -->

    <!-- Export Section -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
        <h5 class="mb-0">Sales Transactions</h5>
        <a href="{{ route('sales-report.export', request()->query()) }}" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-2"></i>Export to Excel
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
                            <th class="d-none d-sm-table-cell">Receipt No</th>
                            <th>Branch name</th>
                            <th class="d-none d-md-table-cell">Total</th>
                            <th class="d-none d-lg-table-cell">Payment</th>
                            <th class="d-none d-xl-table-cell">Cash</th>
                            <th class="d-none d-xl-table-cell">Card</th>
                            <th class="d-none d-xl-table-cell">Credit</th>
                            <th>Date/Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td class="d-none d-sm-table-cell">
                                <span class="badge bg-primary">{{ $sale->receipt_no }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong>{{ $sale->branch->name ?? 'N/A' }}</strong>
                                    <small class="text-muted d-sm-none">{{ $sale->receipt_no }}</small>
                                    <small class="text-muted d-md-none">LKR {{ number_format($sale->subtotal, 2) }}</small>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">LKR {{ number_format($sale->subtotal, 2) }}</td>
                            <td class="d-none d-lg-table-cell">
                                <span class="badge bg-info">{{ $sale->payment_method }}</span>
                            </td>
                            <td class="d-none d-xl-table-cell">LKR {{ number_format(max(0, ($sale->customer_payment ?? 0) - ($sale->balance ?? 0)), 2) }}</td>
                            <td class="d-none d-xl-table-cell">LKR {{ number_format($sale->card_payment ?? 0, 2) }}</td>
                            <td class="d-none d-xl-table-cell">LKR {{ number_format($sale->credit_balance ?? 0, 2) }}</td>
                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button class="btn btn-sm btn-outline-primary view-items-btn" 
                                        data-sale-id="{{ $sale->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#saleItemsModal"
                                        title="View Items">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @if($sale->status ?? 1)
                                    <button class="btn btn-sm btn-outline-danger ms-1 delete-sale-btn" 
                                            data-sale-id="{{ $sale->id }}"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @else
                                    <span class="badge bg-secondary ms-1">Deleted</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach

                        <!-- Totals Row -->
                        <tr class="table-info fw-bold">
                            <td colspan="2" class="text-end">TOTAL :</td>
                            <td class="d-none d-md-table-cell">LKR {{ number_format($totals->total_subtotal ?? 0, 2) }}</td>
                            <td class="d-none d-lg-table-cell">-</td>
                            <td class="d-none d-xl-table-cell">LKR {{ number_format($totals->total_cash ?? 0, 2) }}</td>
                            <td class="d-none d-xl-table-cell">LKR {{ number_format($totals->total_card_payment ?? 0, 2) }}</td>
                            <td class="d-none d-xl-table-cell">LKR {{ number_format($totals->total_credit_balance ?? 0, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
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
                                        <strong>Branch:</strong> <span id="modal-branch-name"></span><br>
                                        <strong>Payment Method:</strong> <span id="modal-payment-method"></span><br>
                                        <strong>Date:</strong> <span id="modal-date"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Subtotal:</strong> LKR <span id="modal-subtotal"></span><br>
                                        <strong>Discount:</strong> LKR <span id="modal-discount"></span><br>
                                        <strong>Tax:</strong> LKR <span id="modal-tax"></span><br>
                                        <strong>Total:</strong> LKR <span id="modal-total"></span>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this sale as deleted?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteConfirmBtn">Yes, Delete</button>
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
                        $('#modal-branch-name').text(response.sale.branch_name || 'N/A');
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
                                    <td>LKR ${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td>LKR ${parseFloat(item.total_price).toFixed(2)}</td>
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

            // Helper to show a Bootstrap alert at the top of the card
            function showAlert(type, message) {
                const alertId = 'dynamic-alert';
                // remove any existing
                $('#' + alertId).remove();
                const alertHtml = `\
                    <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">\
                        ${message}\
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\
                    </div>`;
                $('.card-body').first().prepend(alertHtml);
            }

            // Delete sale (soft-delete via status = 0) using confirmation modal
            $(document).on('click', '.delete-sale-btn', function() {
                const saleId = $(this).data('sale-id');
                // store sale id on confirm button
                $('#deleteConfirmBtn').data('sale-id', saleId);
                // show modal
                const deleteModalEl = document.getElementById('deleteConfirmModal');
                const deleteModal = new bootstrap.Modal(deleteModalEl);
                deleteModal.show();
            });

            // Handle confirm button in modal
            $('#deleteConfirmBtn').off('click').on('click', function() {
                const button = $(this);
                const saleId = button.data('sale-id');
                if (!saleId) return;

                // disable to prevent double clicks
                button.prop('disabled', true);

                $.ajax({
                    url: `{{ url('sales-report/sale') }}/${saleId}/status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: 0
                    }
                }).done(function(resp) {
                    if (resp.success) {
                        // Remove the entire table row for this sale with a fade animation
                        const row = $(`.action-cell[data-sale-id="${saleId}"]`).closest('tr');
                        row.fadeOut(300, function() {
                            $(this).remove();

                            // If table body is empty after removal, show the empty state
                            const tbody = $('table.table tbody');
                            if (tbody.find('tr').length === 0) {
                                // Replace the card body contents with the empty state
                                const emptyHtml = `
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox display-1 text-muted"></i>
                                        <h4 class="text-muted mt-3">No sales found</h4>
                                        <p class="text-muted">Try adjusting your search criteria or date range.</p>
                                    </div>`;
                                // remove table and pagination and show empty
                                $('table.table').closest('.table-responsive').remove();
                                $('.d-flex.justify-content-center.mt-3').remove();
                                $('.card-body').first().html(emptyHtml);
                            }
                        });

                        showAlert('success', 'Sale marked as deleted.');
                        // hide modal
                        const deleteModalEl = document.getElementById('deleteConfirmModal');
                        const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
                        if (deleteModal) deleteModal.hide();
                    } else {
                        showAlert('danger', resp.message || 'Error updating status');
                    }
                }).fail(function(xhr) {
                    let msg = 'Error updating status';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showAlert('danger', msg);
                }).always(function() {
                    button.prop('disabled', false);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>