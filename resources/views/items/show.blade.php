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
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-white">
                                <h4 class="mb-1">
                                    <i class="bi bi-box-seam me-2"></i>
                                    {{ $item->item_name }}
                                </h4>
                                <small class="opacity-75">Item Code: <code class="text-light">{{ $item->item_code }}</code></small>
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
                                    
                                    <span class="fw-bold text-success ms-1">LKR</span>
                                    <h6 class="text-muted mb-1">Price</h6>
                                    <h4 class="text-success mb-0">Rs. {{ number_format($item->price, 2) }}</h4>
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
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Quick Stats -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up text-primary me-2"></i>
                            Quick Stats
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 text-center">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="bi bi-calendar-plus text-primary fs-4"></i>
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Created</small>
                                        <strong>{{ $item->created_at->format('M d, Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="bi bi-arrow-repeat text-info fs-4"></i>
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Updated</small>
                                        <strong>{{ $item->updated_at->format('M d, Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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