@extends('layouts.app')

@section('title', 'Stock Transfer Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Stock Transfer Details</h1>
            @if(auth()->user()->isSupervisor())
                <a href="{{ route('supervisor.stock-transfer.by-status') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Transfers
                </a>
            @else
                <a href="{{ route('stock-transfer.pending') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Pending
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header 
                @if($stockTransfer->status === 'pending') bg-warning text-dark
                @elseif($stockTransfer->status === 'accepted') bg-success text-white
                @else bg-danger text-white
                @endif">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-arrow-right-circle"></i>
                        Transfer #{{ $stockTransfer->id }}
                    </h5>
                    <span class="badge 
                        @if($stockTransfer->status === 'pending') bg-light text-dark
                        @elseif($stockTransfer->status === 'accepted') bg-light text-success
                        @else bg-light text-danger
                        @endif">
                        @if($stockTransfer->status === 'pending')
                            <i class="bi bi-clock"></i> Pending
                        @elseif($stockTransfer->status === 'accepted')
                            <i class="bi bi-check-circle"></i> Accepted
                        @else
                            <i class="bi bi-x-circle"></i> Rejected
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- Transfer Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Transfer Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted"><i class="bi bi-calendar"></i> Date & Time:</td>
                                <td><strong>{{ $stockTransfer->date_time->format('F j, Y \a\t g:i A') }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-building"></i> From Branch:</td>
                                <td><span class="badge bg-primary">{{ $stockTransfer->source_name }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-building"></i> To Branch:</td>
                                <td><span class="badge bg-info">{{ $stockTransfer->toBranch->name }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-person"></i> Requested By:</td>
                                <td>{{ $stockTransfer->creator->name }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Processing Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted"><i class="bi bi-flag"></i> Status:</td>
                                <td>
                                    @if($stockTransfer->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Pending Response
                                        </span>
                                    @elseif($stockTransfer->status === 'accepted')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Accepted
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @if($stockTransfer->processed_at)
                                <tr>
                                    <td class="text-muted"><i class="bi bi-calendar-check"></i> Processed At:</td>
                                    <td><strong>{{ $stockTransfer->processed_at->format('F j, Y \a\t g:i A') }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="bi bi-person-check"></i> Processed By:</td>
                                    <td>{{ $stockTransfer->processor->name ?? 'Unknown' }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-muted"><i class="bi bi-calendar-check"></i> Processed At:</td>
                                    <td><span class="text-muted">Not processed yet</span></td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Notes Section -->
                @if($stockTransfer->notes)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-primary">Transfer Notes</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-chat-text"></i>
                                {{ $stockTransfer->notes }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Rejection Reason -->
                @if($stockTransfer->status === 'rejected' && $stockTransfer->rejection_reason)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-danger">Rejection Reason</h6>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ $stockTransfer->rejection_reason }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Transfer Items -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary">Transfer Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Item Code</th>
                                        <th>Transfer Quantity</th>
                                     
                                        <th>Unit Price</th>
                                        <th>Available at Source</th>
                                        <th>Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockTransfer->transferItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->item->item_name }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $item->item->item_code ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">
                                                    {{ number_format($item->quantity, 0) }}
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <span class="text-success fw-semibold">
                                                    ${{ number_format($item->item->price, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    {{ number_format($item->available_quantity, 0) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $item->item->category ?? 'Uncategorized' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th>
                                            <span class="text-primary fw-bold">
                                                {{ number_format($stockTransfer->transferItems->sum('quantity'), 0) }}
                                            </span>
                                        </th>
                                        <th></th>
                                        <th></th>
                                        <th colspan="2">
                                            
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons for Pending Transfers -->
                @if($stockTransfer->status === 'pending' && !auth()->user()->isSupervisor() && $stockTransfer->to_branch_id === auth()->user()->branch_id)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" 
                                        class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle"></i> Reject Transfer
                                </button>
                                
                                <form action="{{ route('stock-transfer.accept', $stockTransfer) }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-success"
                                            onclick="return confirm('Are you sure you want to accept this stock transfer? This action cannot be undone and will update your inventory.')">
                                        <i class="bi bi-check-circle"></i> Accept Transfer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('stock-transfer.reject', $stockTransfer) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-x-circle text-danger"></i>
                                            Reject Stock Transfer
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>You are about to reject this stock transfer from <strong>{{ $stockTransfer->source_name }}</strong>.</p>
                                        <div class="mb-3">
                                            <label for="rejection_reason" class="form-label">
                                                <strong>Reason for Rejection *</strong>
                                            </label>
                                            <textarea class="form-control" 
                                                      id="rejection_reason"
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
                @endif
            </div>
        </div>
    </div>
</div>
@endsection