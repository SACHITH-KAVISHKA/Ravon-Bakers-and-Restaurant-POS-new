<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Add New Item</h1>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Items
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('items.store') }}" method="POST">
                        @csrf
                        
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="item_code" class="form-label">Item Code</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="item_code" 
                                       name="item_code_display" 
                                       value="{{ $nextItemCode }}" 
                                       readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="item_name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control @error('item_name') is-invalid @enderror" 
                                       id="item_name" name="item_name" value="{{ old('item_name') }}" required>
                                @error('item_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->name }}" {{ old('category') == $category->name ? 'selected' : '' }}>
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
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price') }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>