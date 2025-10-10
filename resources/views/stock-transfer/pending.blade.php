@extends('layouts.app')

@section('title', 'Pending Stock Transfers')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Pending Stock Transfers</h1>
            <div class="text-muted">
                <i class="bi bi-building"></i>
                {{ auth()->user()->branch->name ?? 'No Branch Assigned' }}
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock"></i>
                    Transfers Awaiting Your Response
                </h5>
            </div>
            <div class="card-body">
                @if($pendingTransfers->count() > 0)
                    @foreach($pendingTransfers as $transfer)
                        <div class="card mb-3 border">
                            <div class="card-header bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">
                                            <i class="bi bi-arrow-right-circle text-primary"></i>
                                            Transfer from <strong>{{ $transfer->source_name }}</strong>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i>
                                            {{ $transfer->date_time->format('F j, Y \a\t g:i A') }}
                                            | Requested by: {{ $transfer->creator->name }}
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Pending Response
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($transfer->notes)
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-chat-text"></i>
                                        <strong>Notes:</strong> {{ $transfer->notes }}
                                    </div>
                                @endif
                                
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th>Transfer Quantity</th>
                                                <th>Unit</th>
                                                <th>Available at Source</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transfer->transferItems as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->item->item_name }}</strong>
                                                        @if($item->item->item_code)
                                                            <br><small class="text-muted">Code: {{ $item->item->item_code }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-primary">
                                                            {{ number_format($item->quantity, 0) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $item->item->item_unit }}</td>
                                                    <td>
                                                        <span class="text-muted">
                                                            {{ number_format($item->available_quantity, 2) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total</th>
                                                <th>
                                                    <span class="text-primary">
                                                        {{ number_format($transfer->transferItems->sum('quantity'), 0) }} units
                                                    </span>
                                                </th>
                                                <th colspan="2">
                                                    <span class="text-muted">
                                                        {{ $transfer->total_items }} item(s)
                                                    </span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $transfer->id }}">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                    
                                    <form action="{{ route('stock-transfer.accept', $transfer) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-success"
                                                onclick="return confirm('Are you sure you want to accept this stock transfer? This action cannot be undone.')">
                                            <i class="bi bi-check-circle"></i> Accept Transfer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $transfer->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('stock-transfer.reject', $transfer) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="bi bi-x-circle text-danger"></i>
                                                Reject Stock Transfer
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                                                                    <p>You are about to accept the stock transfer from <strong>{{ $transfer->source_name }}</strong>.</p>
                                            <div class="mb-3">
                                                <label for="rejection_reason{{ $transfer->id }}" class="form-label">
                                                    <strong>Reason for Rejection *</strong>
                                                </label>
                                                <textarea class="form-control" 
                                                          id="rejection_reason{{ $transfer->id }}"
                                                          name="rejection_reason" 
                                                          rows="3" 
                                                          required
                                                          placeholder="Please provide a reason for rejecting this transfer..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-x-circle"></i> Reject Transfer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $pendingTransfers->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No Pending Transfers</h5>
                        <p class="text-muted">You're all caught up! There are no pending stock transfers for your branch.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection