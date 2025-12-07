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
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Placed On (Created)</th>
                                <th>Collect Date</th>
                                <th>Branch</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th> <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>
                                    <span class="fw-bold">
                                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td>{{ $order->customer_name }}</td>

                                <td>
                                    {{ $order->created_at->format('Y-m-d') }} <br>
                                    <small class="text-muted">{{ $order->created_at->format('H:i A') }}</small>
                                </td>

                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ \Carbon\Carbon::parse($order->date_time)->format('Y-m-d H:i A') }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $order->branch->name ?? 'N/A' }} </span>
                                </td>

                                <td class="text-end">Rs. {{ number_format($order->total_amount, 2) }}</td>

                                <td class="text-end text-success">Rs. {{ number_format($order->paid_amount, 2) }}</td>

                                <td class="text-end fw-bold">
                                    @php
                                        // Balance ගණනය කිරීම
                                        $balance = $order->total_amount - $order->paid_amount;
                                    @endphp
                                    <span class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                        Rs. {{ number_format($balance, 2) }}
                                    </span>
                                </td>

                                <td>
                                    @if($order->status == 2)
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('staff.orders.show', $order->id) }}" class="btn btn-sm btn-info text-white" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('pos.index', ['import_order' => $order->id]) }}" class="btn btn-sm btn-success" title="Send to POS">
                                            <i class="bi bi-cart-plus"></i> POS
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
             <div class="card-footer bg-white">{{ $orders->links() }}</div>
        </div>
    </div>
</div>
@endsection
