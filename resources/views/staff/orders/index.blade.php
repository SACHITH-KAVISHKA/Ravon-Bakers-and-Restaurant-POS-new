@extends('layouts.app')

@section('title', 'All Orders')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="fw-bold text-dark">Order Management</h3>
            <a href="{{ route('staff.orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Create New Order
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body py-3">
                <form action="{{ route('staff.orders.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="{{ route('staff.orders.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Order ID</th>
                                <th>Date & Time</th>
                                <th>Branch</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td class="ps-4 fw-bold">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div>{{ $order->date_time->format('Y-m-d') }}</div>
                                    <small class="text-muted">{{ $order->date_time->format('h:i A') }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $order->branch->name }}</span></td>
                                <td>{{ $order->customer_name }}</td>
                                <td>Rs. {{ number_format($order->total_amount, 2) }}</td>
                                <td class="text-success">Rs. {{ number_format($order->paid_amount, 2) }}</td>
                                <td class="text-danger fw-bold">Rs. {{ number_format($order->balance_amount, 2) }}</td>
                                <td>
                                    @if($order->status == 2)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('staff.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center py-5 text-muted">No orders found matching your criteria.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
             <div class="card-footer bg-white">{{ $orders->links() }}</div>
        </div>
    </div>
</div>
@endsection
