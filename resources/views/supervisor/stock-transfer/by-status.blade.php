@extends('layouts.app')

@section('title', 'Stock Transfers by Status')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Stock Transfer Management</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('supervisor.stock-transfer.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list"></i> Simple List
                </a>
                <a href="{{ route('supervisor.stock-transfer.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Transfer
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Status Tabs -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs" id="statusTabs">
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" 
                           href="{{ route('supervisor.stock-transfer.by-status', ['status' => 'all']) }}">
                            <i class="bi bi-list-ul"></i>
                            All Transfers
                            <span class="badge bg-secondary ms-1">{{ $statusCounts['all'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
                           href="{{ route('supervisor.stock-transfer.by-status', ['status' => 'pending']) }}">
                            <i class="bi bi-clock"></i>
                            Pending
                            <span class="badge bg-warning ms-1">{{ $statusCounts['pending'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'accepted' ? 'active' : '' }}" 
                           href="{{ route('supervisor.stock-transfer.by-status', ['status' => 'accepted']) }}">
                            <i class="bi bi-check-circle"></i>
                            Accepted
                            <span class="badge bg-success ms-1">{{ $statusCounts['accepted'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" 
                           href="{{ route('supervisor.stock-transfer.by-status', ['status' => 'rejected']) }}">
                            <i class="bi bi-x-circle"></i>
                            Rejected
                            <span class="badge bg-danger ms-1">{{ $statusCounts['rejected'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                @if($transfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Transfer ID</th>
                                    <th>Date & Time</th>
                                    <th>To Branch</th>
                                    <th>Items</th>
                                    <th>Total Quantity</th>
                                    <th>Status</th>
                                    <th>Processed By</th>
                                    <th>Processed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfers as $transfer)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">#{{ $transfer->id }}</span>
                                        </td>
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
                                            @if($transfer->processor)
                                                <div class="small">
                                                    <i class="bi bi-person"></i>
                                                    {{ $transfer->processor->name }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
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
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('supervisor.stock-transfer.show', $transfer) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if($transfer->status === 'rejected' && $transfer->rejection_reason)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectionModal{{ $transfer->id }}"
                                                            title="View Rejection Reason">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    @if($transfer->status === 'rejected' && $transfer->rejection_reason)
                                        <!-- Rejection Reason Modal -->
                                        <div class="modal fade" id="rejectionModal{{ $transfer->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-x-circle text-danger"></i>
                                                            Rejection Reason - Transfer #{{ $transfer->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-danger">
                                                            <strong>Rejected by:</strong> {{ $transfer->processor->name ?? 'Unknown' }}<br>
                                                            <strong>Date:</strong> {{ $transfer->processed_at->format('F j, Y \a\t g:i A') }}<br>
                                                            <strong>Reason:</strong><br>
                                                            {{ $transfer->rejection_reason }}
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $transfers->appends(['status' => $status])->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        @if($status === 'all')
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Stock Transfers Yet</h5>
                            <p class="text-muted">You haven't created any stock transfer requests yet.</p>
                        @elseif($status === 'pending')
                            <i class="bi bi-clock text-warning" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Pending Transfers</h5>
                            <p class="text-muted">You don't have any pending transfer requests.</p>
                        @elseif($status === 'accepted')
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Accepted Transfers</h5>
                            <p class="text-muted">You don't have any accepted transfer requests yet.</p>
                        @else
                            <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Rejected Transfers</h5>
                            <p class="text-muted">You don't have any rejected transfer requests.</p>
                        @endif
                        
                        <a href="{{ route('supervisor.stock-transfer.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle"></i> Create Your First Transfer
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $statusCounts['all'] }}</h5>
                <p class="text-muted mb-0 small">Total Transfers</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $statusCounts['pending'] }}</h5>
                <p class="text-muted mb-0 small">Pending Transfers</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $statusCounts['accepted'] }}</h5>
                <p class="text-muted mb-0 small">Accepted Transfers</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
            <div class="card-body text-center">
                <div class="text-danger mb-2">
                    <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $statusCounts['rejected'] }}</h5>
                <p class="text-muted mb-0 small">Rejected Transfers</p>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 0.75rem 1rem;
}

.nav-tabs .nav-link.active {
    background-color: #e9ecef;
    border-color: transparent;
    color: #495057;
    font-weight: 600;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #495057;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.table th {
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endsection