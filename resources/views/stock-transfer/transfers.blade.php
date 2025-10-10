@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ $pageTitle }}</h1>
                    <p class="text-muted mb-0">View and manage stock transfers by status</p>
                </div>
                @can('supervisor-access')
                    <a href="{{ route('supervisor.stock-transfer.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create New Transfer
                    </a>
                @endcan
            </div>

            <div class="card shadow-sm">
                <!-- Status Tabs -->
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}">
                                <i class="bi bi-clock text-warning"></i>
                                Pending
                                @if($statusCounts['pending'] > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $statusCounts['pending'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'accepted' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['status' => 'accepted']) }}">
                                <i class="bi bi-check-circle text-success"></i>
                                Accepted
                                @if($statusCounts['accepted'] > 0)
                                    <span class="badge bg-success ms-1">{{ $statusCounts['accepted'] }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}">
                                <i class="bi bi-x-circle text-danger"></i>
                                Rejected
                                @if($statusCounts['rejected'] > 0)
                                    <span class="badge bg-danger ms-1">{{ $statusCounts['rejected'] }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>

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
                                        <th>Created By</th>
                                        @if($status !== 'pending')
                                            <th>Processed By</th>
                                            <th>Processed Date</th>
                                        @endif
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
                                                <div class="small">
                                                    <i class="bi bi-person"></i>
                                                    {{ $transfer->creator->name }}
                                                </div>
                                            </td>
                                            @if($status !== 'pending')
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
                                            @endif
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('stock-transfer.show', $transfer) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    @if($transfer->status === 'pending' && auth()->user()->branch_id === $transfer->to_branch_id)
                                                        <form action="{{ route('stock-transfer.accept', $transfer) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to accept this transfer?')">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-success" 
                                                                    title="Accept Transfer">
                                                                <i class="bi bi-check"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#rejectModal{{ $transfer->id }}"
                                                                title="Reject Transfer">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($transfer->status === 'rejected' && $transfer->rejection_reason)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#rejectionModal{{ $transfer->id }}"
                                                                title="View Rejection Reason">
                                                            <i class="bi bi-info-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $transfers->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted">No {{ ucfirst($status) }} Transfers</h5>
                            <p class="text-muted mb-0">
                                @if($status === 'pending')
                                    There are no pending transfers at the moment.
                                @elseif($status === 'accepted')
                                    No transfers have been accepted yet.
                                @else
                                    No transfers have been rejected.
                                @endif
                            </p>
                            @if($status === 'pending' && auth()->user()->role === 'supervisor')
                                <a href="{{ route('supervisor.stock-transfer.create') }}" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Create First Transfer
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modals -->
@foreach($transfers as $transfer)
    @if($transfer->status === 'pending' && auth()->user()->branch_id === $transfer->to_branch_id)
        <div class="modal fade" id="rejectModal{{ $transfer->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-x-circle text-danger"></i>
                            Reject Transfer #{{ str_pad($transfer->id, 4, '0', STR_PAD_LEFT) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('stock-transfer.reject', $transfer) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>You are about to reject this stock transfer from <strong>{{ $transfer->source_name }}</strong>.</p>
                            <div class="mb-3">
                                <label for="rejection_reason{{ $transfer->id }}" class="form-label">
                                    <strong>Reason for Rejection *</strong>
                                </label>
                                <textarea class="form-control" 
                                          id="rejection_reason{{ $transfer->id }}"
                                          name="rejection_reason" 
                                          rows="4" 
                                          placeholder="Please provide a reason for rejecting this transfer..."
                                          required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Reject Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($transfer->status === 'rejected' && $transfer->rejection_reason)
        <div class="modal fade" id="rejectionModal{{ $transfer->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-info-circle text-danger"></i>
                            Rejection Reason - Transfer #{{ str_pad($transfer->id, 4, '0', STR_PAD_LEFT) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>Rejection Reason:</strong><br>
                            {{ $transfer->rejection_reason }}
                        </div>
                        <div class="small text-muted">
                            Rejected by {{ $transfer->processor->name ?? 'System' }} 
                            on {{ $transfer->processed_at ? $transfer->processed_at->format('F j, Y \a\t g:i A') : 'Unknown' }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection