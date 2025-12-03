@extends('layouts.app')

@section('title', 'Adjustment Details #' . $adjustment->id)

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Adjustment Details <span class="text-muted">#{{ $adjustment->id }}</span></h1>
            <p class="text-muted mb-0">
                Created on {{ \Carbon\Carbon::parse($adjustment->created_at)->format('F d, Y h:i A') }}
            </p>
        </div>
        <a href="{{ route('supervisor.stock-adjustment.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light border-0 h-100">
            <div class="card-body">
                <small class="text-uppercase text-muted fw-bold">Branch</small>
                <p class="h5 mb-0 fw-bold text-primary">{{ $adjustment->branch->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light border-0 h-100">
            <div class="card-body">
                <small class="text-uppercase text-muted fw-bold">Adjustment Date</small>
                <p class="h5 mb-0">{{ \Carbon\Carbon::parse($adjustment->adjustment_date)->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light border-0 h-100">
            <div class="card-body">
                <small class="text-uppercase text-muted fw-bold">Cashier</small>
                <p class="h5 mb-0">{{ $adjustment->cashier_name }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light border-0 h-100">
            <div class="card-body">
                <small class="text-uppercase text-muted fw-bold">Total Variance</small>
                <p class="h5 mb-0 fw-bold {{ $adjustment->total_variance < 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($adjustment->total_variance, 2) }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0 fw-bold">Item Breakdown</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Item Name</th>
                        <th class="text-center">System Qty</th>
                        <th class="text-center">Actual Qty</th>
                        <th class="text-center">Variance Qty</th>
                        <th class="text-end pe-4">Variance Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adjustment->details as $detail)
                    <tr>
                        <td class="ps-4">
                            <span class="fw-medium">{{ $detail->item->item_name ?? 'Item Deleted' }}</span>
                            <br>
                            <small class="text-muted">{{ $detail->item->item_code ?? '' }}</small>
                        </td>
                        <td class="text-center">{{ $detail->current_stock }}</td>
                        <td class="text-center">{{ $detail->actual_stock }}</td>

                        <td class="text-center fw-bold {{ $detail->variance < 0 ? 'text-danger' : ($detail->variance > 0 ? 'text-success' : 'text-muted') }}">
                            {{ $detail->variance > 0 ? '+' : '' }}{{ $detail->variance }}
                        </td>

                        <td class="text-end pe-4 fw-bold {{ $detail->variance_amount < 0 ? 'text-danger' : ($detail->variance_amount > 0 ? 'text-success' : 'text-muted') }}">
                            {{ number_format($detail->variance_amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                        <td class="text-end pe-4 fw-bold {{ $adjustment->total_variance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($adjustment->total_variance, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if($adjustment->notes)
<div class="alert alert-light border mt-4">
    <h6 class="fw-bold"><i class="bi bi-sticky"></i> Notes:</h6>
    <p class="mb-0">{{ $adjustment->notes }}</p>
</div>
@endif

@endsection
