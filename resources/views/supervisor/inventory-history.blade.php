@extends('layouts.app')

@section('title', 'Inventory History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Inventory History</h1>
            <a href="{{ route('supervisor.add-inventory') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Inventory
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i>
                    Your Inventory Additions
                </h5>
            </div>
            <div class="card-body">
                @if($inventoryRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Department</th>
                                    <th>Items Count</th>
                                    <th>Status</th>
                                    <th>Total Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventoryRequests as $request)
                                    <tr>
                                        <td>{{ $request->date_time->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $request->department->name }}
                                            </span>
                                        </td>
                                        <td>{{ $request->inventoryRequestItems->count() }} items</td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->inventoryRequestItems->sum('quantity') }}</td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailsModal{{ $request->id }}">
                                                <i class="bi bi-eye"></i> Show
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $inventoryRequests->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">No Inventory History</h4>
                        <p class="text-muted">You haven't added any inventory yet.</p>
                        <a href="{{ route('supervisor.add-inventory') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Your First Inventory
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Details Modals -->
@foreach($inventoryRequests as $request)
<div class="modal fade" id="detailsModal{{ $request->id }}" tabindex="-1" aria-labelledby="detailsModalLabel{{ $request->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailsModalLabel{{ $request->id }}">
                    <i class="bi bi-list-check"></i>
                    Inventory Details - {{ $request->date_time->format('M d, Y H:i') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Request Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Department:</strong>
                        <span class="badge bg-info ms-1">{{ $request->department->name }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <span class="badge bg-success ms-1">{{ ucfirst($request->status) }}</span>
                    </div>
                </div>
                
                @if($request->notes)
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Notes:</strong> {{ $request->notes }}
                    </div>
                @endif
                
                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Item Code</th>
                                <th>Quantity Added</th>
                                <th>Unit Price</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalValue = 0; @endphp
                            @foreach($request->inventoryRequestItems as $item)
                                @php $itemTotal = $item->quantity * $item->item->price; @endphp
                                @php $totalValue += $itemTotal; @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $item->item->item_name }}</strong>
                                    </td>
                                    <td>
                                        <code>{{ $item->item->item_code }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $item->quantity }}</span>
                                    </td>
                                    <td>
                                        Rs. {{ number_format($item->item->price, 2) }}
                                    </td>
                                    <td>
                                        <strong>Rs. {{ number_format($itemTotal, 2) }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total Items: {{ $request->inventoryRequestItems->count() }}</th>
                                <th>Total Quantity: {{ $request->inventoryRequestItems->sum('quantity') }}</th>
                                <th colspan="2">Total Value: <strong>Rs. {{ number_format($totalValue, 2) }}</strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

<style>
.table-primary th {
    background-color: #6f42c1 !important;
    color: white !important;
    font-weight: 600;
    border: none;
}

.table-hover tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.1);
}

.badge.bg-light {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    border: 1px solid #dee2e6;
}

.badge.bg-success {
    background-color: #198754 !important;
}

.badge.bg-info {
    background-color: #0dcaf0 !important;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
}

.btn-primary {
    background-color: #6f42c1;
    border-color: #6f42c1;
}

.btn-primary:hover {
    background-color: #5a2d91;
    border-color: #5a2d91;
}

.modal-header.bg-primary {
    background-color: #6f42c1 !important;
}

.table code {
    background-color: #f8f9fa;
    color: #e83e8c;
    font-size: 0.875rem;
    padding: 0.25rem 0.375rem;
    border-radius: 0.25rem;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.table-light th {
    background-color: #f8f9fa !important;
    font-weight: 600;
    color: #495057;
}

.table tfoot th {
    background-color: #e9ecef !important;
    font-weight: 700;
    border-top: 2px solid #dee2e6;
}

.modal-lg {
    max-width: 900px;
}

.table-bordered td, .table-bordered th {
    border: 1px solid #dee2e6;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
}

/* Custom styling for consistent purple theme */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* Responsive table adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection