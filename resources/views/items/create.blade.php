<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Add New Item</h1>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Items
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add New Item
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('items.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="item_code" class="form-label fw-semibold">Item Code</label>
                                <input type="text"
                                    class="form-control form-control-lg"
                                    id="item_code"
                                    name="item_code_display"
                                    value="{{ $nextItemCode }}"
                                    readonly>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="item_name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg @error('item_name') is-invalid @enderror"
                                    id="item_name" name="item_name" value="{{ old('item_name') }}" required
                                    placeholder="Enter item name">
                                @error('item_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-12 col-md-6">
                                <label for="category" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('category') is-invalid @enderror"
                                    id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->name }}" @selected(old('category')==$category->name)>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Price moved to branch-specific pricing. See Branch Prices section below. -->
                        </div>

                        <div class="mt-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                id="description" name="description" rows="4"
                                placeholder="Enter item description (optional)">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Branch Prices Section --}}
                        @php
                        // Pass empty array for create view
                        @endphp
                        <input type="hidden" id="__branchPricesForItem" value="[]">
                        @include('items.partials.branch_prices')

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>Create Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>