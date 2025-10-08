<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-box-seam me-2"></i>
            Item Management
        </h1>
        @can('create', App\Models\Item::class)
        <a href="{{ route('items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Item
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><code>{{ $item->item_code }}</code></td>
                            <td>{{ $item->item_name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->category }}</span>
                            </td>
                            <td>Rs. {{ number_format($item->price, 2) }}</td>
                            <td>
                                @if($item->inventory)
                                    @if($item->inventory->isLowStock())
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            {{ $item->inventory->current_stock }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            {{ $item->inventory->current_stock }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('items.show', $item) }}" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $item)
                                    <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $item)
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteItemModal{{ $item->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-box-seam fa-3x mb-3"></i>
                                <div>No items found</div>
                                @can('create', App\Models\Item::class)
                                <small>
                                    <a href="{{ route('items.create') }}">Add your first item</a>
                                </small>
                                @endcan
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
    @can('delete', $item)
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
    @endcan
    @endforeach
</x-app-layout>