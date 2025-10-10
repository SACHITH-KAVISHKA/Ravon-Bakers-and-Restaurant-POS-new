@extends('layouts.app')

@section('title', 'Stock Transfers')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Stock Transfer History</h1>
            <a href="{{ route('supervisor.stock-transfer.by-status') }}" class="btn btn-outline-secondary">
                <i class="bi bi-funnel"></i> View by Status
            </a>
            <a href="{{ route('supervisor.stock-transfer.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Transfer
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul"></i> Transfer Requests
                </h5>
            </div>
            <div class="card-body">
                @if($transfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>To Branch</th>
                                    <th>Items</th>
                                    <th>Total Quantity</th>
                                    <th>Status</th>
                                    <th>Processed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfers as $transfer)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $transfer->date_time->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $transfer->date_time->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <i class="bi bi-building"></i>
                                                {{ $transfer->toBranch->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $transfer->total_items }} items
                                            </span>
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
                                            @if($transfer->processed_at)
                                                <div class="small">
                                                    <div>{{ $transfer->processed_at->format('M d, Y') }}</div>
                                                    <div class="text-muted">{{ $transfer->processed_at->format('h:i A') }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('supervisor.stock-transfer.show', $transfer) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
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
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No Stock Transfers Yet</h5>
                        <p class="text-muted">You haven't created any stock transfer requests yet.</p>
                        <a href="{{ route('supervisor.stock-transfer.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle"></i> Create Your First Transfer
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $transfers->where('status', 'pending')->count() }}</h5>
                <p class="text-muted mb-0 small">Pending Transfers</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $transfers->where('status', 'accepted')->count() }}</h5>
                <p class="text-muted mb-0 small">Accepted Transfers</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-danger mb-2">
                    <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $transfers->where('status', 'rejected')->count() }}</h5>
                <p class="text-muted mb-0 small">Rejected Transfers</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $transfers->count() }}</h5>
                <p class="text-muted mb-0 small">Total Transfers</p>
            </div>
        </div>
    </div>
</div>
@endsection