@extends('layouts.app')

@section('title', 'Add Wastage')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Add Wastage</h1>
            <a href="{{ route('supervisor.wastage-view') }}" class="btn btn-outline-info">
                <i class="bi bi-eye"></i> View Wastage Records
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">
                    <i class="bi bi-trash"></i>
                    Wastage Record Form
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisor.store-wastage') }}" method="POST" id="wastageForm">
                    @csrf
                    
                    <!-- Date & Time Section -->
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
                    </div>

                    <!-- Items Table Section -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Items Table</label>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Items</th>
                                        <th style="width: 20%;">Available Stock</th>
                                        <th style="width: 20%;">Wasted Quantity</th>
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
                                                    <option value="{{ $item['id'] }}" 
                                                            data-stock="{{ $item['available_stock'] }}">
                                                        {{ $item['item_name'] }} ({{ $item['item_code'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('items.0.item_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control stock-display" 
                                                   readonly 
                                                   placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control quantity-input" 
                                                   name="items[0][wasted_quantity]" 
                                                   min="1" 
                                                   required>
                                            @error('items.0.wasted_quantity')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
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

                    <!-- Remarks Section -->
                    <div class="mb-4">
                        <label for="remarks" class="form-label fw-semibold">Remarks (Optional)</label>
                        <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                  id="remarks" 
                                  name="remarks" 
                                  rows="3" 
                                  placeholder="Add any additional remarks about the wastage...">{{ old('remarks') }}</textarea>
                        @error('remarks')
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
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Record Wastage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@endsection

@push('scripts')
<script>
    // Store items data for JavaScript
    window.wastageItems = @json($items->values());
    console.log('Window loaded, items:', window.wastageItems);
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded!');
    
    let rowIndex = 1;
    const addRowBtn = document.getElementById('addRowBtn');
    const itemsTableBody = document.getElementById('itemsTableBody');

    console.log('Button element:', addRowBtn);
    console.log('Table body element:', itemsTableBody);
    console.log('Wastage items loaded:', window.wastageItems);

    if (!addRowBtn) {
        console.error('ERROR: Add button not found!');
        return;
    }

    if (!itemsTableBody) {
        console.error('ERROR: Table body not found!');
        return;
    }

    // Add new row
    addRowBtn.addEventListener('click', function(e) {
        console.log('Add button clicked!');
        e.preventDefault();
        e.stopPropagation();
        
        try {
            const newRow = createNewRow(rowIndex);
            console.log('New row created:', newRow);
            
            itemsTableBody.appendChild(newRow);
            console.log('Row appended to table');
            
            rowIndex++;
            updateRemoveButtons();
            console.log('New row added successfully with index:', rowIndex - 1);
        } catch (error) {
            console.error('Error adding row:', error);
        }
    });

    // Handle item selection change
    itemsTableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-select')) {
            const selectedOption = e.target.selectedOptions[0];
            const stockDisplay = e.target.closest('tr').querySelector('.stock-display');
            const quantityInput = e.target.closest('tr').querySelector('.quantity-input');
            
            if (selectedOption.value) {
                const stock = selectedOption.dataset.stock || '0';
                stockDisplay.value = stock;
                quantityInput.setAttribute('max', stock);
                quantityInput.value = '';
                quantityInput.setCustomValidity('');
            } else {
                stockDisplay.value = '';
                quantityInput.removeAttribute('max');
                quantityInput.value = '';
                quantityInput.setCustomValidity('');
            }
        }
    });

    // Handle quantity input validation
    itemsTableBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const maxStock = parseInt(e.target.getAttribute('max')) || 0;
            const currentValue = parseInt(e.target.value) || 0;
            
            if (currentValue > maxStock && maxStock > 0) {
                e.target.setCustomValidity(`Wasted quantity cannot exceed available stock (${maxStock})`);
            } else if (currentValue < 1 && e.target.value !== '') {
                e.target.setCustomValidity('Wasted quantity must be at least 1');
            } else {
                e.target.setCustomValidity('');
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
        console.log('Creating row with index:', index);
        const row = document.createElement('tr');
        row.className = 'item-row';
        
        // Create options HTML from JavaScript data
        let optionsHtml = '<option value="">Select Item</option>';
        
        if (window.wastageItems && Array.isArray(window.wastageItems)) {
            window.wastageItems.forEach(item => {
                optionsHtml += `<option value="${item.id}" data-stock="${item.available_stock}">${item.item_name} (${item.item_code})</option>`;
            });
        } else {
            console.error('Wastage items not available or not an array');
        }
        
        row.innerHTML = `
            <td>
                <select class="form-select item-select" name="items[${index}][item_id]" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="text" 
                       class="form-control stock-display" 
                       readonly 
                       placeholder="0">
            </td>
            <td>
                <input type="number" 
                       class="form-control quantity-input" 
                       name="items[${index}][wasted_quantity]" 
                       min="1" 
                       required>
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
            quantityInput.name = `items[${index}][wasted_quantity]`;
        });
        rowIndex = rows.length;
    }

    // Initialize remove buttons
    updateRemoveButtons();
    console.log('Initialization complete');

    // Form submission validation
    const wastageForm = document.getElementById('wastageForm');
    if (wastageForm) {
        wastageForm.addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.item-row');
            
            if (rows.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to record wastage.');
                return;
            }
            
            let hasError = false;
            rows.forEach((row, index) => {
                const itemSelect = row.querySelector('.item-select');
                const quantityInput = row.querySelector('.quantity-input');
                
                if (!itemSelect.value) {
                    e.preventDefault();
                    hasError = true;
                    alert(`Please select an item for row ${index + 1}.`);
                    return;
                }
                
                if (!quantityInput.value || parseInt(quantityInput.value) < 1) {
                    e.preventDefault();
                    hasError = true;
                    alert(`Please enter a valid wasted quantity for row ${index + 1}.`);
                    return;
                }
            });
            
            if (hasError) return;
            
            const selectedItems = [];
            rows.forEach((row, index) => {
                const itemId = row.querySelector('.item-select').value;
                if (itemId && selectedItems.includes(itemId)) {
                    e.preventDefault();
                    alert('You cannot select the same item multiple times. Please remove duplicate entries.');
                    return;
                }
                if (itemId) {
                    selectedItems.push(itemId);
                }
            });
        });
    }
});

// Test if the button exists when page loads
window.addEventListener('load', function() {
    console.log('Window load event fired');
    const btn = document.getElementById('addRowBtn');
    console.log('Button on window load:', btn);
});
</script>
@endpush

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.item-row:hover {
    background-color: #f8f9fa;
}

.stock-display {
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

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>