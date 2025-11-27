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
                                        <option value="{{ $category->name }}" @selected(old('category', $item->category) == $category->name)>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Price moved to branch-specific pricing. Use Branch Prices section below to set per-branch rates. -->
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

                            <div class="mb-3">
                                <label for="stock_count" class="form-label">Include in Stock Count *</label>
                                <select class="form-control @error('stock_count') is-invalid @enderror"
                                    id="stock_count" name="stock_count" required>
                                    <option value="1" @selected(old('stock_count', $item->stock_count))>Yes - Include in stock reports</option>
                                    <option value="0" @selected(old('stock_count', $item->stock_count)==='0' || old('stock_count', $item->stock_count)===false)>No - Exclude from stock reports</option>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Select "No" for items like fresh juices or smoothies that don't need stock tracking
                                </small>
                                @error('stock_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Branch Prices Section --}}
                            @php
                            $bps = $item->branchPrices()->with('branch')->get()->map(function($b){ return ['branch_id'=>$b->branch_id,'price'=>$b->price]; });
                            @endphp
                            <script>
                                window.__branchPricesForItem = @json($bps->values());
                            </script>
                            @include('items.partials.branch_prices')
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
</x-app-layout>