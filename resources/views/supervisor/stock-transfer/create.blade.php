@extends('layouts.app')

@section('title', 'Create Stock Transfer')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Create Stock Transfer</h1>
            <a href="{{ route('stock-transfer.transfers') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Transfers
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-arrow-right-circle"></i>
                    Stock Transfer Request
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisor.stock-transfer.store') }}" method="POST" id="transferForm">
                    @csrf
                    
                    <!-- Transfer Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="date_time" class="form-label fw-semibold">
                                <i class="bi bi-calendar"></i> Date & Time
                            </label>
                            <input type="datetime-local" 
                                   class="form-control @error('date_time') is-invalid @enderror" 
                                   id="date_time" 
                                   name="date_time" 
                                   value="{{ old('date_time', now()->format('Y-m-d\TH:i')) }}" 
                                   required>
                            @error('date_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="to_branch_id" class="form-label fw-semibold">
                                <i class="bi bi-building"></i> Destination Branch
                            </label>
                            <select class="form-select @error('to_branch_id') is-invalid @enderror" 
                                    id="to_branch_id" 
                                    name="to_branch_id" 
                                    required>
                                <option value="">Select Destination Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Transferring from Central Inventory to selected branch
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="bi bi-chat-text"></i> Notes (Optional)
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Add any additional notes about this transfer...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-semibold mb-0">
                                    <i class="bi bi-box-seam"></i> Transfer Items
                                </h5>
                                <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                    <i class="bi bi-plus-circle"></i> Add Item
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Item</th>
                                            <th width="20%">Available Qty</th>
                                            <th width="20%">Transfer Qty</th>
                                            <th width="20%">Unit</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        <!-- Dynamic rows will be added here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('supervisor.stock-transfer.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-send"></i> Send Transfer Request
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden template for new item rows -->
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <select class="form-control item-select" name="items[INDEX][item_id]" required>
                <option value="">Select Item</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" data-unit="{{ $item->item_unit }}">
                        {{ $item->item_name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control available-qty" readonly placeholder="0">
        </td>
        <td>
            <input type="number" 
                   class="form-control transfer-qty" 
                   name="items[INDEX][quantity]" 
                   step="0.01" 
                   min="0.01" 
                   placeholder="0.00" 
                   required>
        </td>
        <td>
            <span class="item-unit text-muted">-</span>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-item">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    const itemsTableBody = document.getElementById('itemsTableBody');
    const addItemBtn = document.getElementById('addItemBtn');
    const itemRowTemplate = document.getElementById('itemRowTemplate');
    
    // Add first row on page load
    addItemRow();
    
    // Add item row
    addItemBtn.addEventListener('click', addItemRow);
    
    function addItemRow() {
        const template = itemRowTemplate.content.cloneNode(true);
        const row = template.querySelector('.item-row');
        
        // Replace INDEX placeholder with actual index
        row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);
        
        // Add event listeners
        const itemSelect = row.querySelector('.item-select');
        const transferQty = row.querySelector('.transfer-qty');
        const removeBtn = row.querySelector('.remove-item');
        
        itemSelect.addEventListener('change', function() {
            updateAvailableQuantity(this);
            updateItemUnit(this);
        });
        
        transferQty.addEventListener('input', function() {
            validateTransferQuantity(this);
        });
        
        removeBtn.addEventListener('click', function() {
            if (itemsTableBody.children.length > 1) {
                row.remove();
            } else {
                alert('At least one item is required.');
            }
        });
        
        itemsTableBody.appendChild(row);
        itemIndex++;
    }
    
    function updateAvailableQuantity(selectElement) {
        const itemId = selectElement.value;
        const row = selectElement.closest('.item-row');
        const availableQtyInput = row.querySelector('.available-qty');
        
        if (itemId) {
            fetch(`/supervisor/stock-transfer/api/inventory/${itemId}`)
                .then(response => response.json())
                .then(data => {
                    availableQtyInput.value = data.available_quantity || 0;
                    
                    // Reset transfer quantity if it exceeds available quantity
                    const transferQtyInput = row.querySelector('.transfer-qty');
                    if (parseFloat(transferQtyInput.value) > data.available_quantity) {
                        transferQtyInput.value = '';
                    }
                    transferQtyInput.max = data.available_quantity;
                })
                .catch(error => {
                    console.error('Error fetching inventory:', error);
                    availableQtyInput.value = '0';
                });
        } else {
            availableQtyInput.value = '';
        }
    }
    
    function updateItemUnit(selectElement) {
        const option = selectElement.options[selectElement.selectedIndex];
        const unit = option.getAttribute('data-unit') || '-';
        const row = selectElement.closest('.item-row');
        const unitSpan = row.querySelector('.item-unit');
        unitSpan.textContent = unit;
    }
    
    function validateTransferQuantity(input) {
        const row = input.closest('.item-row');
        const availableQty = parseFloat(row.querySelector('.available-qty').value) || 0;
        const transferQty = parseFloat(input.value) || 0;
        
        if (transferQty > availableQty) {
            input.setCustomValidity(`Transfer quantity cannot exceed available quantity (${availableQty})`);
            input.classList.add('is-invalid');
        } else if (transferQty <= 0) {
            input.setCustomValidity('Transfer quantity must be greater than 0');
            input.classList.add('is-invalid');
        } else {
            input.setCustomValidity('');
            input.classList.remove('is-invalid');
        }
    }
    
    // Form validation before submit
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.item-row');
        let isValid = true;
        
        // Check if at least one item is added
        if (items.length === 0) {
            alert('Please add at least one item to transfer.');
            e.preventDefault();
            return;
        }
        
        // Validate each item row
        items.forEach(row => {
            const itemSelect = row.querySelector('.item-select');
            const transferQty = row.querySelector('.transfer-qty');
            const availableQty = parseFloat(row.querySelector('.available-qty').value) || 0;
            
            if (!itemSelect.value) {
                isValid = false;
                itemSelect.classList.add('is-invalid');
            }
            
            if (!transferQty.value || parseFloat(transferQty.value) <= 0) {
                isValid = false;
                transferQty.classList.add('is-invalid');
            }
            
            if (parseFloat(transferQty.value) > availableQty) {
                isValid = false;
                transferQty.classList.add('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fix the validation errors before submitting.');
        }
    });
});
</script>

<style>
.card {
    border-radius: 15px;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-top: none;
}

.item-row td {
    vertical-align: middle;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
}

.invalid-feedback {
    display: block;
}

.is-invalid {
    border-color: #dc3545;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endsection