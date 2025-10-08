@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Add Inventory</h1>
            <a href="{{ route('supervisor.create-department') }}" class="btn btn-outline-primary">
                <i class="bi bi-building-add"></i> Create New Department
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus-circle"></i>
                    Inventory Request Form
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisor.store-inventory') }}" method="POST" id="inventoryForm">
                    @csrf
                    
                    <!-- Date & Time and Department Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="date_time" class="form-label fw-semibold">Date & Time</label>
                            <input type="datetime-local" 
                                   class="form-control @error('date_time') is-invalid @enderror" 
                                   id="date_time" 
                                   name="date_time" 
                                   value="{{ old('date_time', now()->format('Y-m-d\TH:i')) }}" 
                                   required>
                            @error('date_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="department_id" class="form-label fw-semibold">From (Department)</label>
                            <select class="form-select @error('department_id') is-invalid @enderror" 
                                    id="department_id" 
                                    name="department_id" 
                                    required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" 
                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Items Table Section -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Items Table</label>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%;">Items</th>
                                        <th style="width: 20%;">Qty</th>
                                        <th style="width: 20%;">Rate (Selling Price)</th>
                                        <th style="width: 10%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Initial row -->
                                    <tr class="item-row">
                                        <td>
                                            <select class="form-select item-select" name="items[0][item_id]" required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}" 
                                                            data-price="{{ $item->price }}"
                                                            data-code="{{ $item->item_code }}">
                                                        {{ $item->item_name }} 
                                                        @if($item->inventory)
                                                            (Current: {{ $item->inventory->current_stock }})
                                                        @else
                                                            (Current: 0)
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control quantity-input" 
                                                   name="items[0][quantity]" 
                                                   min="1" 
                                                   required>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control price-display" 
                                                   readonly 
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="button" class="btn btn-success btn-sm mt-2" id="addRowBtn">
                            <i class="bi bi-plus-circle"></i> Add More Items
                        </button>
                    </div>

                    <!-- Notes Section -->
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Add any additional notes or comments...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Submit Section -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('supervisor.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Submit Inventory Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const addRowBtn = document.getElementById('addRowBtn');
    const itemsTableBody = document.getElementById('itemsTableBody');

    // Add new row
    addRowBtn.addEventListener('click', function() {
        const newRow = createNewRow(rowIndex);
        itemsTableBody.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();
    });

    // Handle item selection change
    itemsTableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-select')) {
            const selectedOption = e.target.selectedOptions[0];
            const priceDisplay = e.target.closest('tr').querySelector('.price-display');
            
            if (selectedOption.value) {
                const price = selectedOption.dataset.price || '0.00';
                priceDisplay.value = parseFloat(price).toFixed(2);
            } else {
                priceDisplay.value = '';
            }
        }
    });

    // Handle remove row
    itemsTableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
            updateRemoveButtons();
            updateRowIndices();
        }
    });

    function createNewRow(index) {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>
                <select class="form-select item-select" name="items[${index}][item_id]" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" 
                                data-price="{{ $item->price }}"
                                data-code="{{ $item->item_code }}">
                            {{ $item->item_name }}
                            @if($item->inventory)
                                (Current: {{ $item->inventory->current_stock }})
                            @else
                                (Current: 0)
                            @endif
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" 
                       class="form-control quantity-input" 
                       name="items[${index}][quantity]" 
                       min="1" 
                       required>
            </td>
            <td>
                <input type="text" 
                       class="form-control price-display" 
                       readonly 
                       placeholder="0.00">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        return row;
    }

    function updateRemoveButtons() {
        const rows = itemsTableBody.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-row');
            removeBtn.disabled = rows.length === 1;
        });
    }

    function updateRowIndices() {
        const rows = itemsTableBody.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            const itemSelect = row.querySelector('.item-select');
            const quantityInput = row.querySelector('.quantity-input');
            
            itemSelect.name = `items[${index}][item_id]`;
            quantityInput.name = `items[${index}][quantity]`;
        });
        rowIndex = rows.length;
    }

    // Initialize remove buttons
    updateRemoveButtons();
});
</script>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.item-row:hover {
    background-color: #f8f9fa;
}

.price-display {
    background-color: #e9ecef;
    color: #6c757d;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}
</style>
@endsection