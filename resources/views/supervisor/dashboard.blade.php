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