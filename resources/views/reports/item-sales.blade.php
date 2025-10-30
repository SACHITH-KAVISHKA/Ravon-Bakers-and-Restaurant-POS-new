@extends('layouts.app')

@section('title', 'Sales by Item Summary')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                    <i class="bi bi-graph-up" style="color: #667eea;"></i> Sales by Item Summary
                </h1>
            </div>
        </div>
    </div>

    <!-- Date Range Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="from_date" class="form-label fw-bold">
                                <i class="bi bi-calendar3"></i> From Date
                            </label>
                            <input type="date" class="form-control" id="from_date" name="from_date"
                                value="{{ $fromDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="to_date" class="form-label fw-bold">
                                <i class="bi bi-calendar3"></i> To Date
                            </label>
                            <input type="date" class="form-control" id="to_date" name="to_date"
                                value="{{ $toDate }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2" id="filterBtn"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>
                            <a href="#" id="exportBtn" class="btn btn-success ms-2" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); border: none; color: #fff;">
                                <i class="bi bi-download"></i> Export Excel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading sales data...</p>
    </div>

    <!-- Sales Data Table -->
    <div id="salesTableContainer">
        @include('reports.partials.item-sales-table', ['salesData' => $salesData, 'branches' => $branches])
    </div>
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title" id="itemDetailsModalLabel">
                    <i class="bi bi-receipt"></i> Item Transaction Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Loading Spinner -->
                <div id="detailsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading transaction details...</p>
                </div>

                <!-- Content Container -->
                <div id="detailsContent" style="display: none;">
                    <!-- Branch-wise Transaction Tables -->
                    <div id="branchTablesContainer">
                        <!-- Tables will be inserted here dynamically -->
                    </div>

                    <!-- Grand Total -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="text-white mb-0">
                                <i class="bi bi-calculator"></i> Grand Total Quantity:
                                <span class="badge bg-light text-dark ms-2" id="grandTotalQty" style="font-size: 1.2rem; padding: 0.5rem 1rem;">0</span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="exportItemDetailsBtn" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); border: none;">
                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .sales-table {
        font-size: 0.95rem;
    }

    .sales-table thead th {
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
        border-bottom: 2px solid #dee2e6 !important;
        background-color: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .sales-table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05) !important;
        transition: all 0.2s ease-in-out;
    }

    .sales-table tbody td {
        vertical-align: middle;
    }

    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15) !important;
    }

    .badge-quantity {
        font-weight: 600;
        min-width: 50px;
        display: inline-block;
        font-size: 0.9rem;
    }

    .form-label {
        color: #495057;
        font-size: 0.9rem;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }

    .modal-header {
        border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    }

    .modal-body .card {
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .view-details-btn {
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .view-details-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    /* Table animations */
    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05) !important;
    }

    /* Responsive table adjustments */

    /* Responsive table adjustments */
    @media (max-width: 768px) {
        .sales-table {
            font-size: 0.85rem;
        }

        .sales-table thead th {
            padding: 0.5rem !important;
            font-size: 0.75rem;
        }

        .sales-table tbody td {
            padding: 0.5rem !important;
        }
    }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        // Store current item details for export
        let currentItemDetails = {
            itemId: null,
            itemCode: null,
            itemName: null,
            fromDate: null,
            toDate: null
        };

        // Update export link with current dates
        function updateExportLink() {
            const fromDate = $('#from_date').val();
            const toDate = $('#to_date').val();
            const exportUrl = '{{ route("reports.item-sales.export") }}' + '?from_date=' + fromDate + '&to_date=' + toDate;
            $('#exportBtn').attr('href', exportUrl);
        }

        // Initialize export link
        updateExportLink();

        // Update export link when dates change
        $('#from_date, #to_date').on('change', function() {
            updateExportLink();
        });

        // Handle View Details button click
        $(document).on('click', '.view-details-btn', function() {
            const itemId = $(this).data('item-id');
            const itemCode = $(this).data('item-code');
            const itemName = $(this).data('item-name');
            const fromDate = $('#from_date').val();
            const toDate = $('#to_date').val();

            // Store current item details for export
            currentItemDetails = {
                itemId: itemId,
                itemCode: itemCode,
                itemName: itemName,
                fromDate: fromDate,
                toDate: toDate
            };

            // Update modal title
            $('#itemDetailsModalLabel').html(`
            <i class="bi bi-receipt"></i> ${itemName} (${itemCode}) - Transaction Details
        `);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
            modal.show();

            // Show loading, hide content
            $('#detailsLoading').show();
            $('#detailsContent').hide();

            // Fetch item details via AJAX
            $.ajax({
                url: '{{ route("reports.item-sales.details") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    item_id: itemId,
                    from_date: fromDate,
                    to_date: toDate
                },
                success: function(response) {
                    if (response.success) {
                        displayItemDetails(response.branches, response.total_quantity);
                    }
                },
                error: function(xhr) {
                    $('#branchTablesContainer').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Error loading transaction details. Please try again.
                    </div>
                `);
                    $('#detailsContent').show();
                },
                complete: function() {
                    $('#detailsLoading').hide();
                    $('#detailsContent').show();
                }
            });
        });

        // Function to display item details
        function displayItemDetails(branches, totalQuantity) {
            let tablesHtml = '';

            if (branches.length === 0) {
                tablesHtml = `
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
                    <p class="mt-3 mb-0">No transactions found for this item in the selected date range.</p>
                </div>
            `;
            } else {
                // Create a table for each branch
                branches.forEach(function(branchData, index) {
                    const branchColor = getBranchColor(index);

                    tablesHtml += `
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header text-white" style="background: ${branchColor};">
                            <h6 class="mb-0">
                                <i class="bi bi-building"></i> ${branchData.branch_name}
                                <span class="badge bg-light text-dark ms-2">
                                    ${branchData.transactions.length} transaction${branchData.transactions.length > 1 ? 's' : ''}
                                </span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead style="background-color: #f8f9fa;">
                                        <tr>
                                            <th class="px-3 py-2">Receipt No</th>
                                            <th class="px-3 py-2 text-center">Quantity</th>
                                            <th class="px-3 py-2 text-end">Unit Price</th>
                                            <th class="px-3 py-2 text-end">Total Price</th>
                                            <th class="px-3 py-2">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                    // Add transaction rows
                    branchData.transactions.forEach(function(transaction) {
                        tablesHtml += `
                        <tr>
                            <td class="px-3 py-2">
                                <span class="badge bg-primary">${transaction.receipt_no}</span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="badge bg-success">${transaction.quantity}</span>
                            </td>
                            <td class="px-3 py-2 text-end">LKR ${parseFloat(transaction.unit_price).toFixed(2)}</td>
                            <td class="px-3 py-2 text-end fw-bold">LKR ${parseFloat(transaction.total_price).toFixed(2)}</td>
                            <td class="px-3 py-2 text-muted small">${transaction.date}</td>
                        </tr>
                    `;
                    });

                    // Add branch total row
                    tablesHtml += `
                                    </tbody>
                                    <tfoot style="background-color: #fff3cd; border-top: 2px solid #ffc107;">
                                        <tr>
                                            <td colspan="1" class="px-3 py-3 fw-bold text-dark" style="font-size: 1rem;">
                                                <i class="bi bi-calculator"></i> Branch Total:
                                            </td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="badge bg-warning text-dark" style="font-size: 1.1rem; padding: 0.5rem 1rem;">
                                                    ${branchData.total_quantity}
                                                </span>
                                            </td>
                                            <td colspan="3" class="px-3 py-3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                });
            }

            // Update the container
            $('#branchTablesContainer').html(tablesHtml);
            $('#grandTotalQty').text(totalQuantity);
        }

        // Helper function to get branch colors
        function getBranchColor(index) {
            const colors = [
                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'linear-gradient(135deg, #764ba2 0%, #667eea 100%)',
                'linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%)',
                'linear-gradient(135deg, #a855f7 0%, #9333ea 100%)',
                'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
                'linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%)',
                'linear-gradient(135deg, #9333ea 0%, #7e22ce 100%)',
                'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)',
            ];
            return colors[index % colors.length];
        }

        // Handle form submission via AJAX
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            const fromDate = $('#from_date').val();
            const toDate = $('#to_date').val();

            // Validate dates
            if (new Date(fromDate) > new Date(toDate)) {
                alert('From Date cannot be after To Date');
                return;
            }

            // Show loading spinner
            $('#loadingSpinner').show();
            $('#salesTableContainer').hide();

            // Make AJAX request
            $.ajax({
                url: '{{ route("reports.item-sales.filter") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    from_date: fromDate,
                    to_date: toDate
                },
                success: function(response) {
                    if (response.success) {
                        // Update date display
                        const fromDateFormatted = new Date(fromDate).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        const toDateFormatted = new Date(toDate).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });

                        $('#displayFromDate').text(fromDateFormatted);
                        $('#displayToDate').text(toDateFormatted);

                        // Build table HTML
                        updateTable(response.data, response.branches);
                    }
                },
                error: function(xhr) {
                    alert('Error loading data. Please try again.');
                    console.error(xhr);
                },
                complete: function() {
                    // Hide loading spinner
                    $('#loadingSpinner').hide();
                    $('#salesTableContainer').show();
                }
            });
        });

        // Reset button
        $('#resetBtn').on('click', function() {
            const today = new Date().toISOString().split('T')[0];
            $('#from_date').val(today);
            $('#to_date').val(today);
            $('#filterForm').submit();
        });

        // Function to update table with new data
        function updateTable(salesData, branches) {
            let tableHtml = `
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-table"></i> Item Sales Summary
                                <span class="badge bg-light text-dark ms-2">${salesData.length} items</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 sales-table">
                                    <thead>
                                        <tr>
                                            <th class="border-0 px-3 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff;">Item No</th>
                                            <th class="border-0 px-3 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff;">Item Name</th>
                                            <th class="border-0 px-3 py-3 text-center" style="background-color: #e3f2fd; color: #1976d2;">Total Qty</th>`;

            // Add branch columns
            branches.forEach(function(branch) {
                tableHtml += `<th class="border-0 px-3 py-3 text-center" style="background-color: #fff3e0; color: #e65100;">${branch.name}</th>`;
            });

            tableHtml += `
                                            <th class="border-0 px-3 py-3 text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

            if (salesData.length === 0) {
                tableHtml += `
                <tr>
                    <td colspan="${4 + branches.length}" class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No sales data available for the selected date range.</p>
                    </td>
                </tr>`;
            } else {
                salesData.forEach(function(item, index) {
                    const rowClass = item.total_quantity === 0 ? 'table-secondary' : '';
                    tableHtml += `
                    <tr class="${rowClass}">
                        <td class="px-3 py-3" style="background-color: #f3e8ff;">
                            <code class="text-dark" style="font-weight:700; color: #6a4bc0;">${item.item_code}</code>
                        </td>
                        <td class="px-3 py-3" style="background-color: #faf5ff;">
                            <span class="fw-bold" style="color: #6a4bc0; font-size: 0.98rem;">${item.item_name}</span>
                        </td>
                        <td class="px-3 py-3 text-center" style="background-color: #f5f9ff;">
                            <span class="badge badge-quantity" style="background-color: #2196f3; color: white;">
                                ${item.total_quantity}
                            </span>
                        </td>`;

                    branches.forEach(function(branch) {
                        const qty = item.branches[branch.name] || 0;
                        const bgColor = qty > 0 ? '#ff9800' : '#e0e0e0';
                        const textColor = qty > 0 ? 'white' : '#757575';

                        tableHtml += `
                        <td class="px-3 py-3 text-center" style="background-color: #fffbf5;">
                            <span class="badge badge-quantity" style="background-color: ${bgColor}; color: ${textColor};">
                                ${qty}
                            </span>
                        </td>`;
                    });

                    tableHtml += `
                        <td class="px-3 py-3 text-center" style="background-color: #f8f9fa;">
                            <button class="btn btn-sm btn-outline-primary view-details-btn" 
                                    data-item-id="${item.item_id}"
                                    data-item-code="${item.item_code}"
                                    data-item-name="${item.item_name}"
                                    title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }

            tableHtml += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

            $('#salesTableContainer').html(tableHtml);
        }

        // Handle Export Item Details button click
        $('#exportItemDetailsBtn').on('click', function() {
            if (!currentItemDetails.itemId) {
                alert('No item data available to export');
                return;
            }

            // Create export URL with item details
            const exportUrl = '{{ route("reports.item-sales.export-item-details") }}' + 
                '?item_id=' + currentItemDetails.itemId +
                '&from_date=' + currentItemDetails.fromDate +
                '&to_date=' + currentItemDetails.toDate;

            // Trigger download
            window.location.href = exportUrl;
        });
    });
</script>
@endpush
@endsection