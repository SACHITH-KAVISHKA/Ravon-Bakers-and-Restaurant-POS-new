@extends('layouts.app')

@section('title', 'Production Item Summary')

@section('content')

{{-- 1. To handle dynamic routes --}}
@php
    $isSupervisor = request()->routeIs('supervisor.*');

    // Form Action URL
    $filterRoute = $isSupervisor
        ? route('supervisor.reports.production-summary')
        : route('reports.production-summary');

    // Export URL
    $exportRoute = $isSupervisor
        ? route('supervisor.reports.production-summary.export', request()->query())
        : route('reports.production-summary.export', request()->query());

    // AJAX URL for Modal
    $detailsAjaxRoute = $isSupervisor
        ? route('supervisor.reports.production-item-details')
        : route('reports.production-item-details');
@endphp

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                <i class="bi bi-clipboard-check" style="color: #667eea;"></i> Production Item Summary
            </h1>
            {{-- Export Button Placeholder --}}
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

            <div class="col-md-4">
                <label for="start_date" class="form-label fw-semibold text-muted small">From Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
            </div>

            <div class="col-md-4">
                <label for="end_date" class="form-label fw-semibold text-muted small">To Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ $filterRoute }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
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
                        <th class="text-center">Count of Batches</th>
                        <th class="text-center">Last Production Date</th>
                        <th class="text-end pe-4">Total Quantity</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($productionItems as $item)
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
                                <span class="badge bg-light text-dark border">{{ $item->request_count }}</span>
                            </td>

                            <td class="text-center text-muted small">
                                {{ \Carbon\Carbon::parse($item->last_production_date)->format('Y-m-d h:i A') }}
                            </td>

                            <td class="text-end pe-4">
                                <h5 class="mb-0 fw-bold text-success">{{ number_format($item->total_quantity) }}</h5>
                            </td>

                            {{-- NEW ACTION COLUMN --}}
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-view-details"
                                        data-item-id="{{ $item->id }}"
                                        data-item-name="{{ $item->item_name }}"
                                        title="View Breakdown">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        {{-- ... empty state ... --}}
                        @endforelse
                    </tbody>
                @if($productionItems->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Page Total:</td>
                        <td class="text-end pe-4 fw-bold text-primary">
                            {{ number_format($productionItems->sum('total_quantity')) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <div class="mt-4">
            {{ $productionItems->links() }}
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalTitle">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalLoader" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="modalContent" class="table-responsive">
                    {{-- Content loads here via AJAX --}}
                </div>
            </div>
            <div class="modal-footer p-1">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('itemDetailsModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        const modalLoader = document.getElementById('modalLoader');

        // Current filter dates from the main form inputs
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');

        document.querySelectorAll('.btn-view-details').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const itemName = this.getAttribute('data-item-name');
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;

                // Setup Modal
                modalTitle.textContent = 'Breakdown: ' + itemName;
                modalContent.innerHTML = '';
                modalLoader.style.display = 'block';
                modal.show();

                // Fetch Data
                const url = `{{ $detailsAjaxRoute }}?item_id=${itemId}&start_date=${startDate}&end_date=${endDate}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        modalLoader.style.display = 'none';
                        modalContent.innerHTML = data.html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        modalLoader.style.display = 'none';
                        modalContent.innerHTML = '<p class="text-danger text-center">Failed to load data.</p>';
                    });
            });
        });
    });
</script>
@endpush
@endsection
