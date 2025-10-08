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