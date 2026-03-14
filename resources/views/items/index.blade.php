<x-app-layout>
    {{-- Default Tax Rates (Controller eken awe neththan use wenawa) --}}
    @php
        $vRate = $vatRate ?? 18;
        $sRate = $ssclRate ?? 2.5;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-box-seam me-2"></i>
            Item Management
        </h1>

        @if(isset($canManageItems) && $canManageItems)
        <a href="{{ route('items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Item
        </a>
        @endif
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('items.index') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label fw-semibold">Search Items</label>
                    <input type="text" class="form-control" id="search" name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by item name, code, or category...">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                        @if(request('search'))
                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Clear
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4 d-none d-md-table-cell">Item Code</th>
                            <th>Item Name</th>
                            <th class="d-none d-lg-table-cell">Category</th>

                            @foreach($branches as $branch)
                            @if($branch->id !== 1)
                            <th class="text-center d-none d-lg-table-cell">
                                {{ $branch->name }}<br>
                                <small class="fw-normal text-muted" style="font-size: 10px;">(Base + Tax = Total)</small>
                            </th>
                            @endif
                            @endforeach

                            <th class="text-center d-none d-sm-table-cell">Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="ps-4 d-none d-md-table-cell"><code>{{ $item->item_code }}</code></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark">{{ $item->item_name }}</strong>
                                    <small class="text-muted d-md-none">{{ $item->item_code }}</small>
                                    {{-- Tax Badges --}}
                                    <div class="mt-1">
                                        @if($item->vat_applicable)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 9px;">VAT {{ $vRate }}%</span>
                                        @endif
                                        @if($item->sscl_applicable)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 9px;">SSCL {{ $sRate }}%</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <span class="badge bg-light text-secondary border">{{ $item->category }}</span>
                            </td>

                            {{-- Tax Logic for Each Branch Column --}}
                            @foreach($branches as $branch)
                            @if($branch->id !== 1)
                            @php $bp = $item->branchPrices->firstWhere('branch_id', $branch->id); @endphp
                            <td class="text-center d-none d-lg-table-cell">
                                @if($bp)
                                    @php
                                        $sellingPrice = (float)$bp->price; // DB eke thiyena mudala

                                        // Tax Factor eka hadaganeema
                                        $factor = 1;
                                        if($item->vat_applicable) $factor += ($vRate / 100);
                                        if($item->sscl_applicable) $factor += ($sRate / 100);

                                        // Backward Calculation
                                        $basePrice = $sellingPrice / $factor;
                                        $totalTax = $sellingPrice - $basePrice;
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-success">LKR {{ number_format($sellingPrice, 2) }}</span>
                                        <div class="d-flex justify-content-center gap-2 mt-1" style="font-size: 11px;">
                                            <span class="text-secondary" title="Base Price">B: {{ number_format($basePrice, 2) }}</span>
                                            <span class="text-danger" title="Total Tax">T: {{ number_format($totalTax, 2) }}</span>
                                        </div>
                                    </div>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                            </td>
                            @endif
                            @endforeach

                            <td class="text-center d-none d-sm-table-cell">
                                @if($item->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                                @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Inactive</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group btn-group-sm">
                                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'director')
                                    <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <button type="button" class="btn btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteItemModal{{ $item->id }}"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 6 + $branches->count() }}" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No items found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    @foreach($items as $item)
    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'director')
    <div class="modal fade" id="deleteItemModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteItemModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-danger text-white">
                    <h5 class="modal-title" id="deleteItemModalLabel{{ $item->id }}">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="bg-danger bg-opacity-10 rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-trash text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="mb-3">Are you sure you want to delete this item?</h6>
                        <div class="bg-light rounded p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="text-start">
                                    <small class="text-muted">Item Code:</small>
                                    <div><code>{{ $item->item_code }}</code></div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Item Name:</small>
                                    <div class="fw-semibold">{{ $item->item_name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        Cancel
                    </button>
                    <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-trash me-1"></i>
                            Delete Item
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</x-app-layout>
