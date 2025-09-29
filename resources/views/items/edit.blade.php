<x-app-layout>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Edit Item
                        </h4>
                    </div>
                    
                    <form action="{{ route('items.update', $item) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="item_code" class="form-label">Item Code</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="item_code" 
                                           name="item_code" 
                                           value="{{ $item->item_code }}" 
                                           readonly>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Item code cannot be changed after creation
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="item_name" class="form-label">Item Name *</label>
                                    <input type="text" 
                                           class="form-control @error('item_name') is-invalid @enderror" 
                                           id="item_name" 
                                           name="item_name" 
                                           value="{{ old('item_name', $item->item_name) }}" 
                                           required>
                                    @error('item_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category *</label>
                                    <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}" {{ old('category', $item->category) == $category->name ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price (Rs.) *</label>
                                    <input type="number" 
                                           class="form-control @error('price') is-invalid @enderror" 
                                           id="price" 
                                           name="price" 
                                           step="0.01" 
                                           min="0" 
                                           value="{{ old('price', $item->price) }}" 
                                           required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                    <input type="number" 
                                           class="form-control @error('stock_quantity') is-invalid @enderror" 
                                           id="stock_quantity" 
                                           name="stock_quantity" 
                                           min="0" 
                                           value="{{ old('stock_quantity', $item->stock_quantity) }}" 
                                           required>
                                    @error('stock_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Current stock: {{ $item->getCurrentStock() }} units
                                        @if($item->hasLowStock())
                                            <span class="badge bg-warning ms-2">Low Stock</span>
                                        @elseif($item->isOutOfStock())
                                            <span class="badge bg-danger ms-2">Out of Stock</span>
                                        @endif
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="low_stock_alert" class="form-label">Low Stock Alert *</label>
                                    <input type="number" 
                                           class="form-control @error('low_stock_alert') is-invalid @enderror" 
                                           id="low_stock_alert" 
                                           name="low_stock_alert" 
                                           min="0" 
                                           value="{{ old('low_stock_alert', $item->inventory ? $item->inventory->low_stock_alert : 0) }}" 
                                           required>
                                    @error('low_stock_alert')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="Enter item description">{{ old('description', $item->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('items.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-lg me-1"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>
                                    Update Item
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                @can('manage-users')
                <!-- Quick Stock Adjustment Card (Admin Only) -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-box-seam me-2"></i>
                            Quick Stock Adjustment
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="stockAdjustmentForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="adjustment_quantity" class="form-label">Quantity</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="adjustment_quantity" 
                                           name="stock_quantity" 
                                           min="1" 
                                           required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="adjustment_operation" class="form-label">Operation</label>
                                    <select class="form-control" id="adjustment_operation" name="operation" required>
                                        <option value="">Select Operation</option>
                                        <option value="add">Add Stock (+)</option>
                                        <option value="subtract">Reduce Stock (-)</option>
                                        <option value="set">Set Exact Stock (=)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="adjustment_reason" class="form-label">Reason</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="adjustment_reason" 
                                           name="reason" 
                                           placeholder="e.g., New delivery, Damaged items">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-info">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    Adjust Stock
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan
                
                <!-- Help Text -->
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        All fields marked with * are required
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    @can('manage-users')
    <script>
        document.getElementById('stockAdjustmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const itemId = {{ $item->id }};
            
            fetch(`/items/${itemId}/update-stock`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = 
                        data.message + '. New stock: ' + data.new_stock + ' units' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    
                    // Insert alert at the top of the stock adjustment card
                    const cardBody = document.querySelector('#stockAdjustmentForm').closest('.card-body');
                    cardBody.insertBefore(alertDiv, cardBody.firstChild);
                    
                    // Reset form
                    this.reset();
                    
                    // Update the current stock display
                    const stockInfo = document.querySelector('.text-muted');
                    if (stockInfo) {
                        window.location.reload(); // Simple reload to update stock display
                    }
                } else {
                    // Show error message
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating stock.');
            });
        });
    </script>
    @endcan
</x-app-layout>