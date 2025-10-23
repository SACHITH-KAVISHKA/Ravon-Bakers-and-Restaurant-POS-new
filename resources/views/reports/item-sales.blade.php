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
                            <a href="{{ route('reports.item-sales.export', ['from_date' => $fromDate ?? null, 'to_date' => $toDate ?? null]) }}" class="btn btn-success ms-2" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); border: none; color: #fff;">
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
                                        </tr>
                                    </thead>
                                    <tbody>`;
        
                if (salesData.length === 0) {
            tableHtml += `
                <tr>
                    <td colspan="${3 + branches.length}" class="text-center py-5">
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
                
                tableHtml += `</tr>`;
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
});
</script>
@endpush
@endsection
