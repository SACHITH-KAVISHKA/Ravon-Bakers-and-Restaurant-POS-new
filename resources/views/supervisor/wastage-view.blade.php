@extends('layouts.app')

@section('title', 'Wastage Records')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Wastage Records</h1>
            <a href="{{ route('supervisor.add-wastage') }}" class="btn btn-danger">
                <i class="bi bi-plus-circle"></i> Add Wastage
            </a>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="card-title mb-0">
                    <i class="bi bi-funnel"></i> Filters
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('supervisor.wastage-view') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item_name" 
                                   name="item_name" 
                                   placeholder="Search by item name..." 
                                   value="{{ request('item_name') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('supervisor.wastage-view') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Clear Filters
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Wastage Records Section -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="card-title mb-0">
                    <i class="bi bi-list-ul"></i> Wastage Records
                </h6>
            </div>
            <div class="card-body">
                @if($wastages->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Items Count</th>
                                    <th>Total Wasted</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($wastages as $wastage)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar3 text-muted me-2"></i>
                                                {{ $wastage->date_time->format('M d, Y H:i') }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $wastage->wastageItems->count() }} items
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $wastage->wastageItems->sum('wasted_quantity') }} units
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $wastage->remarks ? Str::limit($wastage->remarks, 50) : 'No remarks' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-outline-info btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#wastageModal{{ $wastage->id }}">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $wastages->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h5 class="text-muted mt-3">No wastage records found</h5>
                        <p class="text-muted">
                            @if(request()->hasAny(['date_from', 'date_to', 'item_name']))
                                Try adjusting your filters or 
                                <a href="{{ route('supervisor.wastage-view') }}">clear all filters</a>
                            @else
                                Start by <a href="{{ route('supervisor.add-wastage') }}">adding a wastage record</a>
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Wastage Detail Modals -->
@foreach($wastages as $wastage)
    <div class="modal fade" id="wastageModal{{ $wastage->id }}" tabindex="-1" aria-labelledby="wastageModalLabel{{ $wastage->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wastageModalLabel{{ $wastage->id }}">
                        <i class="bi bi-trash"></i> Wastage Details - {{ $wastage->date_time->format('M d, Y H:i') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date & Time:</strong>
                            <p class="mb-0">{{ $wastage->date_time->format('F d, Y \a\t H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Recorded by:</strong>
                            <p class="mb-0">{{ $wastage->user->name }}</p>
                        </div>
                    </div>
                    
                    @if($wastage->remarks)
                        <div class="mb-3">
                            <strong>Remarks:</strong>
                            <p class="mb-0">{{ $wastage->remarks }}</p>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong>Wasted Items:</strong>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Previous Stock</th>
                                        <th>Wasted Quantity</th>
                                        <th>Remaining Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wastage->wastageItems as $wastageItem)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $wastageItem->item->item_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $wastageItem->item->item_code }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $wastageItem->previous_stock }}</td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    {{ $wastageItem->wasted_quantity }}
                                                </span>
                                            </td>
                                            <td>{{ $wastageItem->remaining_stock }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th>{{ $wastage->wastageItems->sum('previous_stock') }}</th>
                                        <th>
                                            <span class="badge bg-danger">
                                                {{ $wastage->wastageItems->sum('wasted_quantity') }}
                                            </span>
                                        </th>
                                        <th>{{ $wastage->wastageItems->sum('remaining_stock') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@endsection