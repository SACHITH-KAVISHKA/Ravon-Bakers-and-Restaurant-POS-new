@extends('layouts.app')

@section('title', 'My Accepted Items')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="bi bi-check-square" style="color: #667eea;"></i> My Accepted Items
                </h1>
                <a href="{{ route('staff.pending-inventory-requests') }}" class="btn btn-outline-primary">
                    <i class="bi bi-clock"></i> Pending Requests
                </a>
            </div>
        </div>
    </div>

    @if($acceptedItems->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-check"></i>
                    Items You Have Accepted
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Department</th>
                                <th>Accepted Date</th>
                                <th>Request Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acceptedItems as $acceptedItem)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $acceptedItem->item->item_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $acceptedItem->item->item_name }}</strong>
                                        @if($acceptedItem->item->description)
                                            <br><small class="text-muted">{{ $acceptedItem->item->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $acceptedItem->quantity }}</span>
                                    </td>
                                    <td>{{ $acceptedItem->inventoryRequest->department->name }}</td>
                                    <td>
                                        <i class="bi bi-calendar-check text-success"></i>
                                        {{ $acceptedItem->received_at->format('M d, Y H:i') }}
                                    </td>
                                    <td>{{ $acceptedItem->inventoryRequest->date_time->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $acceptedItems->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h4 class="text-muted">No Accepted Items Yet</h4>
            <p class="text-muted">You haven't accepted any inventory items yet.</p>
            <a href="{{ route('staff.pending-inventory-requests') }}" class="btn btn-primary">
                <i class="bi bi-clock"></i> View Pending Requests
            </a>
        </div>
    @endif
</div>
@endsection