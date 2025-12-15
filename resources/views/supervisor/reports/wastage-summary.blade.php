@extends('layouts.app')

@section('title', 'Wastage Summary Report')

@section('content')

@php
    // Determine user role to set correct routes
    $isSupervisor = request()->routeIs('supervisor.*');

    // Filter Route
    $filterRoute = $isSupervisor
        ? route('supervisor.reports.wastage-summary')
        : route('reports.wastage-summary');

    // Export Route
    $exportRoute = $isSupervisor
        ? route('supervisor.reports.wastage-summary.export', request()->query())
        : route('reports.wastage-summary.export', request()->query());

    // AJAX Detail Route
    $detailsAjaxRoute = $isSupervisor
        ? route('supervisor.reports.wastage-item-details')
        : route('reports.wastage-item-details');
@endphp

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                    <i class="bi bi-trash" style="color: #667eea;"></i> Wastage Summary Report
                </h1>
            <a href="{{ $exportRoute }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export All Data
            </a>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-light">
        <form action="{{ $filterRoute }}" method="GET" class="row g-3">
            {{-- Date Inputs... --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold text-muted small">From Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-muted small">To Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
            </div>

            {{-- NEW: Branch Dropdown --}}
            <div class="col-md-3">
                <label for="branch_id" class="form-label fw-semibold text-muted small">Branch</label>
                <select name="branch_id" id="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ $filterRoute }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35%;">Item Information</th>
                        <th class="text-center">Transactions Count</th>
                        <th class="text-center">Last Wastage Date</th>
                        <th class="text-end pe-4">Total Wasted Qty</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wastageItems as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="ms-2">
                                    <h6 class="mb-0 fw-bold text-dark">{{ $item->item_name }}</h6>
                                    <small class="text-muted">{{ $item->item_code ?? 'No Code' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $item->wastage_count }}</span>
                        </td>
                        <td class="text-center text-muted small">
                            {{ \Carbon\Carbon::parse($item->last_wastage_date)->format('Y-m-d h:i A') }}
                        </td>
                        <td class="text-end pe-4">
                            <h5 class="mb-0 fw-bold text-danger">{{ number_format($item->total_wasted) }}</h5>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-view-details"
                                    data-item-id="{{ $item->id }}"
                                    data-item-name="{{ $item->item_name }}"
                                    title="View Breakdown">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4">No wastage records found for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $wastageItems->links() }}
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalTitle">Wastage Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalLoader" class="text-center py-3">
                    <div class="spinner-border text-danger" role="status"></div>
                </div>
                <div id="modalContent" class="table-responsive"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        const modalLoader = document.getElementById('modalLoader');

        document.querySelectorAll('.btn-view-details').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const itemName = this.getAttribute('data-item-name');

                // Get values from inputs
                const startDate = document.querySelector('input[name="start_date"]').value;
                const endDate = document.querySelector('input[name="end_date"]').value;
                const branchId = document.querySelector('select[name="branch_id"]').value; // Get Branch ID

                modalTitle.textContent = 'Wastage: ' + itemName;
                modalContent.innerHTML = '';
                modalLoader.style.display = 'block';
                modal.show();

                // Append branch_id to the URL
                const url = `{{ $detailsAjaxRoute }}?item_id=${itemId}&start_date=${startDate}&end_date=${endDate}&branch_id=${branchId}`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        modalLoader.style.display = 'none';
                        modalContent.innerHTML = data.html;
                    })
                    .catch(err => {
                        console.error(err);
                        modalLoader.style.display = 'none';
                        modalContent.innerHTML = '<p class="text-danger text-center">Failed to load.</p>';
                    });
            });
        });
    });
</script>
@endpush
@endsection
