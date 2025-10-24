@extends('layouts.app')

@section('title', 'My Stock Transfers')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Stock Transfers</h1>
                    <p class="text-muted mb-0">View and manage stock transfers you've created</p>
                </div>
                <a href="{{ route('staff.stock-transfer.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Transfer
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    @if($transfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Transfer ID</th>
                                    <th>Date & Time</th>
                                    <th>Source</th>
                                    <th>Destination</th>
                                    <th>Items</th>
                                    <th>Total Quantity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfers as $transfer)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">#{{ str_pad($transfer->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="fw-semibold">{{ $transfer->date_time->format('M d, Y') }}</div>
                                            <div class="text-muted">{{ $transfer->date_time->format('h:i A') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $transfer->source_name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $transfer->toBranch->name }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $transfer->transferItems->count() }}</span>
                                        <small class="text-muted">items</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($transfer->transferItems->sum('quantity'), 0) }}</span>
                                    </td>
                                    <td>
                                        @if($transfer->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                        @elseif($transfer->status === 'accepted')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Accepted
                                        </span>
                                        @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Rejected
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('stock-transfer.show', $transfer) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $transfers->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">No stock transfers found.</p>
                        <a href="{{ route('staff.stock-transfer.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Your First Transfer
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
