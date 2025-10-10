@extends('layouts.app')

@section('title', 'Supervisor Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Supervisor Dashboard</h1>
            <div class="text-muted">
                <i class="bi bi-calendar-event"></i>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Inventory Requests
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalRequests }}</div>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-clipboard-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Inventory Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventoryCount }}</div>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-boxes fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Low Stock Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockItems }}</div>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Total Wastage Records
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalWastages }}</div>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-trash fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Stock Transfers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTransfers }}</div>
                    </div>
                    <div class="text-info">
                        <i class="bi bi-arrow-left-right fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Transfers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingTransfers }}</div>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-clock-history fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Inventory Requests -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history"></i>
                        Recent Inventory Requests
                    </h5>
                    <a href="{{ route('supervisor.inventory-history') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-list"></i> View All
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($recentRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Department</th>
                                    <th>Items Count</th>
                                    <th>Status</th>
                                    <th>Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRequests as $request)
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No inventory requests yet.</p>
                        <a href="{{ route('supervisor.add-inventory') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Your First Inventory
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Wastage Records -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-trash"></i>
                        Recent Wastage Records
                    </h5>
                    <a href="{{ route('supervisor.wastage-view') }}" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-list"></i> View All
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($recentWastages->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Items Count</th>
                                    <th>Total Wasted</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentWastages as $wastage)
                                    <tr>
                                        <td>{{ $wastage->date_time->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $wastage->wastageItems->count() }} items
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $wastage->wastageItems->sum('wasted_quantity') }} units
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $wastage->remarks ? Str::limit($wastage->remarks, 30) : 'No remarks' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-trash text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No wastage records yet.</p>
                        <a href="{{ route('supervisor.add-wastage') }}" class="btn btn-danger">
                            <i class="bi bi-plus-circle"></i> Add Your First Wastage Record
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Stock Transfers -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-arrow-left-right"></i>
                        Recent Stock Transfers
                    </h5>
                    <a href="{{ route('stock-transfer.transfers') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-list"></i> View All
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($recentTransfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>To Branch</th>
                                    <th>Items Count</th>
                                    <th>Status</th>
                                    <th>Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransfers as $transfer)
                                    <tr>
                                        <td>{{ $transfer->date_time->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $transfer->toBranch->name }}
                                            </span>
                                        </td>
                                        <td>{{ $transfer->transferItems->count() }} items</td>
                                        <td>
                                            @if($transfer->status === 'pending')
                                                <span class="badge bg-warning text-dark">
                                                    {{ ucfirst($transfer->status) }}
                                                </span>
                                            @elseif($transfer->status === 'accepted')
                                                <span class="badge bg-success">
                                                    {{ ucfirst($transfer->status) }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    {{ ucfirst($transfer->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $transfer->transferItems->sum('quantity') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-arrow-left-right text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-3">No stock transfers yet.</h6>
                        <p class="text-muted mt-2">No stock transfers have been created yet.</p>
                        <a href="{{ route('supervisor.stock-transfer.create') }}" class="btn btn-info">
                            <i class="bi bi-plus-circle"></i> Create Your First Transfer
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.text-xs {
    font-size: 0.75rem;
}

.fa-2x {
    font-size: 2rem;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endsection