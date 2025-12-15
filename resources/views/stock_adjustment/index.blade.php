@extends('layouts.app')

@section('title', 'Stock Adjustments History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                <i class="bi bi-sliders" style="color: #667eea;"></i> Stock Adjustments History
            </h1>
            <a href="{{ route('supervisor.stock-adjustment.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Adjustment
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-light">
        <form action="{{ route('supervisor.stock-adjustment.index') }}" method="GET" class="row g-3">

            <div class="col-md-3">
                <label for="start_date" class="form-label fw-semibold text-muted small">From Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
            </div>

            <div class="col-md-3">
                <label for="end_date" class="form-label fw-semibold text-muted small">To Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
            </div>

            <div class="col-md-3">
                <label for="branch_id" class="form-label fw-semibold text-muted small">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('supervisor.stock-adjustment.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </div>

        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Branch</th>
                        <th>Cashier</th>
                        <th>Supervisor</th>
                        <th class="text-end">Total Variance</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr>
                        <td>#{{ $adj->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($adj->adjustment_date)->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ $adj->branch->name ?? 'Unknown' }}
                            </span>
                        </td>
                        <td>{{ $adj->cashier_name }}</td>
                        <td>{{ $adj->supervisor->name ?? 'N/A' }}</td>

                        <td class="text-end fw-bold {{ $adj->total_variance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($adj->total_variance, 2) }}
                        </td>

                        <td class="text-center">
                            <a href="{{ route('supervisor.stock-adjustment.show', $adj->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No stock adjustments found for the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $adjustments->links() }}
        </div>
    </div>
</div>
@endsection
