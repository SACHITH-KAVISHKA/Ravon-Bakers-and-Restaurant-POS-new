@extends('layouts.app')

@section('title', 'Branch Inventory Status')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="bi bi-building" style="color: #667eea;"></i> {{ auth()->user()->branch->name ?? 'Branch' }} Inventory Status
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('pos.index') }}" class="btn btn-primary">
                        <i class="bi bi-calculator"></i> Go to POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($inventoryItems->count() > 0)
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Items
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventoryItems->count() }}</div>
                            </div>
                            <div class="text-primary">
                                <i class="bi bi-box-seam fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    In Stock
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventoryItems->where('current_stock', '>', 0)->count() }}</div>
                            </div>
                            <div class="text-success">
                                <i class="bi bi-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Low Stock
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventoryItems->filter(function($item) { return $item->isLowStock(); })->count() }}</div>
                            </div>
                            <div class="text-warning">
                                <i class="bi bi-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Out of Stock
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inventoryItems->where('current_stock', 0)->count() }}</div>
                            </div>
                            <div class="text-danger">
                                <i class="bi bi-x-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul"></i>
                    Current Stock Levels
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Low Stock Alert</th>
                                <th>Status</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryItems as $inventory)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $inventory->item->item_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $inventory->item->item_name }}</strong>
                                        @if($inventory->item->description)
                                            <br><small class="text-muted">{{ $inventory->item->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $inventory->item->category }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $inventory->current_stock > 0 ? 'bg-primary' : 'bg-danger' }}">
                                            {{ $inventory->current_stock }}
                                        </span>
                                    </td>
                                    <td>{{ $inventory->low_stock_alert }}</td>
                                    <td>
                                        @if($inventory->current_stock == 0)
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Out of Stock
                                            </span>
                                        @elseif($inventory->isLowStock())
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-exclamation-triangle"></i> Low Stock
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> In Stock
                                            </span>
                                        @endif
                                    </td>
                                    <td>Rs. {{ number_format($inventory->item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $inventoryItems->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h4 class="text-muted">No Inventory Items</h4>
            <p class="text-muted">You don't have any items in your branch inventory yet. Contact your supervisor to add inventory to your branch.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-house"></i> Go to Dashboard
            </a>
        </div>
    @endif
</div>
@endsection