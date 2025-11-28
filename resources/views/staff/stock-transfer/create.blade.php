@extends('layouts.app')

@section('title', 'Create Stock Transfer')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle"></i>
                    <strong>Transfer from:</strong> {{ Auth::user()->branch->name ?? 'Your Branch' }}
                </div>

                <form action="{{ route('staff.stock-transfer.store') }}" method="POST" id="transferForm">
                    @csrf

                    <!-- Transfer Details -->
                    <div class="row mb-4">
                        <div class="col-md-4">
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

                        <div class="col-md-4">
                            <label for="to_branch_id" class="form-label fw-semibold">
                                <i class="bi bi-building"></i> Destination Branch
                            </label>
                            <select class="form-select @error('to_branch_id') is-invalid @enderror"
                                id="to_branch_id"
                                name="to_branch_id"
                                required>
                                <option value="">Select Destination Branch</option>
                                @foreach($branches as $branch)
                                {{-- @if($branch->id != 1) --}}
                                <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                                {{-- @endif --}}
                                @endforeach
                            </select>
                            @error('to_branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <button type="button" class="btn btn-success" id="addAllItemsBtn">
                                    <i class="bi bi-plus-circle"></i> Add All Available Items
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="35%">Item</th>
                                            <th width="25%">Available Qty</th>
                                            <th width="25%">Transfer Qty</th>
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
                                <a href="{{ route('stock-transfer.transfers') }}" class="btn btn-outline-secondary">
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
                <option value="{{ $item->id }}" data-available="{{ $item->inventory->first()->current_stock ?? 0 }}">
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
                placeholder="0.00"
                required>
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
        const itemRowTemplate = document.getElementById('itemRowTemplate');

        // CSRF token (read from meta tag) and fetch options for sending cookies
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        const fetchOptions = {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        };

        // Modal helper functions
        function showInfoModal(message) {
            document.getElementById('infoModalBody').innerHTML = '<p class="mb-0">' + message + '</p>';
            new bootstrap.Modal(document.getElementById('infoModal')).show();
        }

        function showErrorModal(message) {
            document.getElementById('errorModalBody').innerHTML = '<p class="mb-0">' + message + '</p>';
            new bootstrap.Modal(document.getElementById('errorModal')).show();
        }

        function showWarningModal(message) {
            document.getElementById('warningModalBody').innerHTML = '<p class="mb-0">' + message + '</p>';
            new bootstrap.Modal(document.getElementById('warningModal')).show();
        }

        // Add first row on page load
        addItemRow();

        // Handle row addition when Tab key is pressed on quantity input
        itemsTableBody.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('transfer-qty') && e.key === 'Tab' && !e.shiftKey) {
                const rows = Array.from(itemsTableBody.querySelectorAll('.item-row'));
                const currentRow = e.target.closest('.item-row');
                const isLastRow = rows[rows.length - 1] === currentRow;

                // Add new row when Tab is pressed on the last row
                if (isLastRow) {
                    e.preventDefault();
                    addItemRow();

                    // Focus on the item select dropdown of the new row
                    setTimeout(() => {
                        const newRows = itemsTableBody.querySelectorAll('.item-row');
                        const newRow = newRows[newRows.length - 1];
                        const newItemSelect = newRow.querySelector('.item-select');
                        if (newItemSelect) {
                            newItemSelect.focus();
                        }
                    }, 10);
                }
            }
        }, true);

        // Add All Available Items button handler
        document.getElementById('addAllItemsBtn').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';

            fetch('/staff/stock-transfer/api/all-inventory', fetchOptions)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        showErrorModal('Error: ' + data.error);
                    } else if (data.items && data.items.length > 0) {
                        // Clear existing rows
                        itemsTableBody.innerHTML = '';
                        itemIndex = 0;

                        // Add all items
                        data.items.forEach(item => {
                            addItemRowWithData(item.item_id, item.item_name, item.available_quantity);
                        });

                        // Don't add empty row after loading all items
                    } else {
                        showInfoModal('<i class="bi bi-inbox"></i> No items found in your branch inventory.<br><small class="text-muted">Please check if your branch has any items with stock.</small>');
                    }
                })
                .catch(error => {
                    console.error('Error fetching inventory:', error);
                    showErrorModal('<strong>Failed to load inventory items</strong><br>' + error.message + '<br><small class="text-muted">Please try again or contact support if the issue persists.</small>');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-plus-circle"></i> Add All Available Items';
                });
        });

        function addItemRowWithData(itemId, itemName, availableQty) {
            const template = itemRowTemplate.content.cloneNode(true);
            const row = template.querySelector('.item-row');

            // Replace INDEX placeholder with actual index
            row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);

            // Get elements
            const itemSelectTd = row.querySelector('td:first-child');
            const availableQtyInput = row.querySelector('.available-qty');
            const transferQtyInput = row.querySelector('.transfer-qty');
            const removeBtn = row.querySelector('.remove-item');

            // Replace select with item name display and hidden input
            itemSelectTd.innerHTML = `
            <input type="hidden" name="items[${itemIndex}][item_id]" value="${itemId}">
            <div class="form-control" readonly style="background-color: #e9ecef;">${itemName}</div>
        `;

            // Set values
            availableQtyInput.value = availableQty;
            transferQtyInput.value = availableQty;
            transferQtyInput.max = availableQty;

            // Add event listeners
            transferQtyInput.addEventListener('input', function() {
                validateTransferQuantity(this);
            });

            removeBtn.addEventListener('click', function() {
                if (itemsTableBody.children.length > 1) {
                    row.remove();
                } else {
                    showWarningModal('At least one item is required for the transfer.');
                }
            });

            itemsTableBody.appendChild(row);
            itemIndex++;
        }

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
            });

            transferQty.addEventListener('input', function() {
                validateTransferQuantity(this);
            });

            removeBtn.addEventListener('click', function() {
                if (itemsTableBody.children.length > 1) {
                    row.remove();
                } else {
                    showWarningModal('At least one item is required for the transfer.');
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
                fetch(`/staff/stock-transfer/api/inventory/${itemId}`, fetchOptions)
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`HTTP ${response.status}: ${text}`);
                            });
                        }
                        return response.json();
                    })
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

        function validateTransferQuantity(input) {
            const row = input.closest('.item-row');
            const availableQty = parseFloat(row.querySelector('.available-qty').value) || 0;
            const transferQty = parseFloat(input.value) || 0;

            // Allow any value including negative quantities
            // Only validate that it's a valid number
            if (isNaN(transferQty)) {
                input.classList.add('is-invalid');
                if (!row.querySelector('.invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Please enter a valid quantity';
                    input.parentNode.appendChild(feedback);
                }
            } else {
                input.classList.remove('is-invalid');
                const feedback = row.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.remove();
                }
            }
        }

        // Form validation before submit
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            const rows = itemsTableBody.querySelectorAll('.item-row');
            let hasValidItem = false;

            // Remove empty rows (rows without item selected)
            rows.forEach(row => {
                const itemSelect = row.querySelector('.item-select');
                const hiddenItemInput = row.querySelector('input[type="hidden"][name*="[item_id]"]');
                const hasItem = (itemSelect && itemSelect.value) || (hiddenItemInput && hiddenItemInput.value);

                if (!hasItem) {
                    row.remove();
                }
            });

            // Re-query rows after removing empty ones
            const validRows = itemsTableBody.querySelectorAll('.item-row');

            validRows.forEach(row => {
                const itemSelect = row.querySelector('.item-select');
                const hiddenItemInput = row.querySelector('input[type="hidden"][name*="[item_id]"]');
                const transferQty = row.querySelector('.transfer-qty');

                // Check if item exists (either via select or hidden input) and has valid quantity
                const hasItem = (itemSelect && itemSelect.value) || (hiddenItemInput && hiddenItemInput.value);
                if (hasItem && !isNaN(parseFloat(transferQty.value))) {
                    hasValidItem = true;
                }
            });

            if (!hasValidItem) {
                e.preventDefault();
                showWarningModal('Please add at least one item with a valid quantity.');
                return false;
            }

            // Check for invalid quantities
            const invalidInputs = itemsTableBody.querySelectorAll('.transfer-qty.is-invalid');
            if (invalidInputs.length > 0) {
                e.preventDefault();
                showErrorModal('<strong>Validation Error</strong><br>Please fix the highlighted errors before submitting the form.');
                return false;
            }
        });
    });
</script>
<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="infoModalLabel">
                    <i class="bi bi-info-circle"></i> Information
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="infoModalBody">
                <!-- Message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">
                    <i class="bi bi-exclamation-triangle"></i> Error
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="errorModalBody">
                <!-- Error message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="warningModalLabel">
                    <i class="bi bi-exclamation-circle"></i> Warning
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="warningModalBody">
                <!-- Warning message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection
