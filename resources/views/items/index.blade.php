<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-box-seam me-2"></i>
            Item Management
        </h1>

        {{-- බොත්තම පෙන්වීම Controller එකෙන් එවන $canManageItems මත තීරණය වේ --}}
        @if(isset($canManageItems) && $canManageItems)
        <a href="{{ route('items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Item
        </a>
        @endif
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
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
                            <i class="bi bi-x-circle me-2"></i>Clear Search
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th class="d-none d-md-table-cell">Item Code</th>
                            <th>Item Name</th>
                            <th class="d-none d-lg-table-cell">Category</th>
                            <!-- Price removed: use branch-specific prices instead -->
                            {{-- one header column per branch --}}
                            @foreach($branches as $branch)
                            @if($branch->id !== 1)
                            <th class="text-center d-none d-lg-table-cell">{{ $branch->name }}</th>
                            @endif
                            @endforeach
                            <th class="d-none d-sm-table-cell">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="d-none d-md-table-cell"><code>{{ $item->item_code }}</code></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong>{{ $item->item_name }}</strong>
                                    <small class="text-muted d-md-none">{{ $item->item_code }}</small>
                                    <small class="text-muted d-lg-none">{{ $item->category }}</small>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <span class="badge bg-secondary">{{ $item->category }}</span>
                            </td>
                            <!-- Price removed: use branch-specific prices instead -->

                            {{-- prices per branch (each branch column) --}}
                            @foreach($branches as $branch)
                            @if($branch->id !== 1)
                            @php $bp = $item->branchPrices->firstWhere('branch_id', $branch->id); @endphp
                            <td class="text-center d-none d-lg-table-cell">
                                @if($bp)
                                LKR {{ number_format($bp->price, 2) }}
                                @else
                                <small class="text-muted">—</small>
                                @endif
                            </td>
                            @endif
                            @endforeach

                            <td class="d-none d-sm-table-cell">
                                @if($item->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    {{-- Allow Admin and Director to Edit/Delete --}}
                                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'director')
                                    <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <button type="button" class="btn btn-outline-danger btn-sm"
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
                            <td colspan="{{ 6 + $branches->count() }}" class="text-center text-muted py-4">
                                <i class="bi bi-box-seam fa-3x mb-3"></i>
                                @if(request('search'))
                                <div>No items found matching "{{ request('search') }}"</div>
                                <small>
                                    <a href="{{ route('items.index') }}">Clear search to view all items</a>
                                </small>
                                @else
                                <div>No items found</div>
                                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'director')
                                <small>
                                    <a href="{{ route('items.create') }}">Add your first item</a>
                                </small>
                                @endif
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modals -->
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
