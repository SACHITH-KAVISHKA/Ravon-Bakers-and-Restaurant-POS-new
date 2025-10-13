@extends('layouts.app')

@section('title', 'Pending Inventory Requests')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="bi bi-clipboard-check" style="color: #667eea;"></i> Pending Inventory Requests
                </h1>
                <a href="{{ route('staff.my-accepted-items') }}" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                    <i class="bi bi-check-square"></i> My Accepted Items
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($pendingRequests->count() > 0)
    <div class="row">
        @foreach($pendingRequests as $request)
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar"></i>
                            Request #{{ $request->id }} - {{ $request->date_time->format('M d, Y H:i') }}
                        </h5>
                        <span class="badge bg-primary">{{ $request->department->name }}</span>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-person"></i> Requested by: {{ $request->user->name }}
                    </small>
                </div>
                <div class="card-body">
                    @if($request->notes)
                    <p class="text-muted mb-3">
                        <i class="bi bi-sticky"></i> <strong>Notes:</strong> {{ $request->notes }}
                    </p>
                    @endif

                    <form action="{{ route('staff.accept-inventory-items', $request) }}" method="POST" id="form-{{ $request->id }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" class="form-check-input select-all" data-form="form-{{ $request->id }}">
                                        </th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Will Add to Branch</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request->inventoryRequestItems as $item)
                                    <tr>
                                        <td>
                                            @if(!$item->received_by)
                                            <input type="checkbox" name="items[]" value="{{ $item->id }}" class="form-check-input item-checkbox">
                                            @endif
                                        </td>
                                        <td>{{ $item->item->item_code }}</td>
                                        <td>{{ $item->item->item_name }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $item->quantity }}</span>
                                        </td>
                                        <td>
                                            @if(!$item->received_by)
                                            <span class="text-success">
                                                <i class="bi bi-plus-circle"></i> +{{ $item->quantity }} to {{ auth()->user()->branch->name ?? 'Your Branch' }}
                                            </span>
                                            @else
                                            <span class="text-muted">Already accepted</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->received_by)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check"></i> Accepted by {{ $item->receivedBy->name }}
                                            </span>
                                            @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                {{ $request->inventoryRequestItems->where('received_by', null)->count() }} items pending acceptance
                            </small>
                            <button type="submit" class="btn btn-success" id="accept-btn-{{ $request->id }}" disabled>
                                <i class="bi bi-check-circle"></i> Accept Selected Items
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $pendingRequests->links() }}
    </div>
    @else
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #6c757d;"></i>
        </div>
        <h4 class="text-muted">No Pending Inventory Requests</h4>
        <p class="text-muted">There are no inventory requests waiting for acceptance at the moment.</p>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle select all functionality
        document.querySelectorAll('.select-all').forEach(function(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const formId = this.getAttribute('data-form');
                const form = document.getElementById(formId);
                const itemCheckboxes = form.querySelectorAll('.item-checkbox');
                const acceptButton = form.querySelector('[id^="accept-btn-"]');

                itemCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });

                // Update button state
                acceptButton.disabled = !selectAllCheckbox.checked;
            });
        });

        // Handle individual checkbox changes
        document.querySelectorAll('.item-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const form = this.closest('form');
                const checkedBoxes = form.querySelectorAll('.item-checkbox:checked');
                const acceptButton = form.querySelector('[id^="accept-btn-"]');
                const selectAllCheckbox = form.querySelector('.select-all');

                // Update accept button state
                acceptButton.disabled = checkedBoxes.length === 0;

                // Update select all checkbox state
                const allCheckboxes = form.querySelectorAll('.item-checkbox');
                selectAllCheckbox.checked = checkedBoxes.length === allCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < allCheckboxes.length;
            });
        });
    });
</script>
@endsection