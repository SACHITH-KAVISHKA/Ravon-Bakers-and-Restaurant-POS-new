<x-app-layout>
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('items.index') }}" class="text-decoration-none">Items</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $item->item_name }}</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Item Details -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1">
                                    <i class="bi bi-box-seam me-2"></i>
                                    {{ $item->item_name }}
                                </h4>
                                <small class="text-white-50">Item Code: <code class="text-white">{{ $item->item_code }}</code></small>
                            </div>
                            <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }} fs-6 px-3 py-2">
                                <i class="bi {{ $item->is_active ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i>
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Key Information Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="bi bi-tags-fill text-primary fs-3 mb-2"></i>
                                    <h6 class="text-muted mb-1">Category</h6>
                                    <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2">{{ $item->category }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="bi bi-cash-coin text-success fs-3 mb-2"></i>
                                    <h6 class="text-muted mb-1">Price</h6>
                                    <a href="{{ route('items.prices.index', $item) }}" class="btn btn-outline-primary">
                                        View branch-specific prices
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($item->description)
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-0">
                                    <i class="bi bi-card-text text-secondary me-2"></i>
                                    Description
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                <p class="mb-0 text-muted">{{ $item->description }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Branch-specific Prices Summary -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-0">
                                    <i class="bi bi-geo-alt-fill text-secondary me-2"></i>
                                    Branch Prices
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                @if($item->branchPrices && $item->branchPrices->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th>Branch</th>
                                                <th>Price (LKR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->branchPrices as $bp)
                                            <tr>
                                                <td>{{ $bp->branch->name ?? '—' }}</td>
                                                <td>LKR {{ number_format($bp->price, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <p class="mb-0 text-muted">No branch-specific prices set for this item.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Action Buttons -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">
                            <i class="bi bi-gear text-secondary me-2"></i>
                            Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @can('update', $item)
                            <a href="{{ route('items.edit', $item) }}" class="btn btn-warning">
                                <i class="bi bi-pencil-square me-2"></i>
                                Edit Item
                            </a>
                            @endcan

                            @can('delete', $item)
                            <form action="{{ route('items.destroy', $item) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Are you sure you want to delete this item?')">
                                    <i class="bi bi-trash me-2"></i>
                                    Delete Item
                                </button>
                            </form>
                            @endcan

                            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Back to Items
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>