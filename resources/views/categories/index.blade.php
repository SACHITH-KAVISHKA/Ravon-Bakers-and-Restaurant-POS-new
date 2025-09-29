@extends('layouts.app')

@section('title', 'Category Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-1">
                        <i class="bi bi-tags me-2"></i>
                        Category Management
                    </h2>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Add Category Form (Left Panel) -->
        @can('create', App\Models\Category::class)
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add New Category
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Enter category name" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                Create Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Categories List (Right Panel) -->
        <div class="@can('create', App\Models\Category::class) col-lg-8 @else col-12 @endcan">
            <div class="card shadow-sm border-0">
                
                <div class="card-body p-0">
                    @if($categories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Name</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Created</th>
                                        @canany(['update', 'delete'], App\Models\Category::class)
                                        <th class="px-4 py-3 text-center">Actions</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $index => $category)
                                    <tr>
                                        <td class="px-4 py-3 fw-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <span class="fw-medium">{{ $category->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge {{ $category->isActive() ? 'bg-success' : 'bg-danger' }}">
                                                {{ $category->isActive() ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <small class="text-muted">
                                                {{ $category->created_at->format('M d, Y') }}
                                            </small>
                                        </td>
                                        @canany(['update', 'delete'], $category)
                                        <td class="px-4 py-3">
                                            <div class="d-flex justify-content-center gap-1">
                                                @can('update', $category)
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editCategoryModal{{ $category->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                @endcan
                                                
                                                @can('delete', $category)
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteCategoryModal{{ $category->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                        @endcanany
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No Categories Found</h5>
                            <p class="text-muted">Start by creating your first category.</p>
                            @can('create', App\Models\Category::class)
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-lg me-1"></i>
                                Add First Category
                            </button>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modals -->
@foreach($categories as $category)
@can('update', $category)
<div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name_{{ $category->id }}" class="form-label">Category Name</label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_name_{{ $category->id }}" 
                               name="name" 
                               value="{{ $category->name }}" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endforeach

<!-- Delete Category Modals -->
@foreach($categories as $category)
@can('delete', $category)
<div class="modal fade" id="deleteCategoryModal{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-3">
                    Are you sure you want to delete the category <strong>"{{ $category->name }}"</strong>?
                </p>
                <p class="text-center text-muted small">
                    This will mark the category as inactive, not permanently delete it.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan
@endforeach

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-close alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }
        }, 5000);
    });
});
</script>
@endsection