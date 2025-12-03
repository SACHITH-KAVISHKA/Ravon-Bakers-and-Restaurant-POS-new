@extends('layouts.app')

@section('title', 'Create Stock Adjustment')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Create Stock Adjustment</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-data"></i>
                    Stock Adjustment Details
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisor.stock-adjustment.store') }}" method="POST" id="adjustmentForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="adjustment_date" class="form-label fw-semibold">
                                <i class="bi bi-calendar"></i> Date & Time
                            </label>
                            <input type="datetime-local"
                                   name="adjustment_date"
                                   id="adjustment_date"
                                   class="form-control @error('adjustment_date') is-invalid @enderror"
                                   value="{{ old('adjustment_date', now()->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('adjustment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="branch_select" class="form-label fw-semibold">
                                <i class="bi bi-building"></i> Branch
                            </label>
                            <select name="branch_id"
                                    id="branch_select"
                                    class="form-select @error('branch_id') is-invalid @enderror"
                                    required
                                    onchange="onBranchChange()">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="cashier_name" class="form-label fw-semibold">
                                <i class="bi bi-person-badge"></i> Cashier Name
                            </label>
                            <input type="text"
                                   name="cashier_name"
                                   id="cashier_name"
                                   class="form-control @error('cashier_name') is-invalid @enderror"
                                   value="{{ old('cashier_name') }}"
                                   placeholder="Enter Name"
                                   required>
                            @error('cashier_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="bi bi-chat-text"></i> Notes (Optional)
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any additional details...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-semibold mb-0">
                                    <i class="bi bi-box-seam"></i> Adjustment Items
                                </h5>
                                <small class="text-muted">Tip: Press <b>Tab</b> on the last "Actual Qty" field to add a new row.</small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="adjustmentTable">
                                    <thead class="table-primary text-white">
                                        <tr>
                                            <th width="30%">Item</th>
                                            <th width="10%">Price</th>
                                            <th width="10%">System Qty</th>
                                            <th width="12%">Actual Qty</th>
                                            <th width="10%">Var Qty</th>
                                            <th width="15%">Var Amount</th>
                                            <th width="5%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        </tbody>
                                    <tfoot>
                                        <tr class="bg-light fw-bold">
                                            <td colspan="5" class="text-end align-middle">Total Variance Amount:</td>
                                            <td>
                                                <input type="text" id="grand_total_amount" class="form-control fw-bold" value="0.00" readonly tabindex="-1">
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Adjustment
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[INDEX][item_id]" class="form-select item-select" required onchange="updateItemDetails(this)">
                <option value="">Select Item</option>
                </select>
        </td>
        <td><input type="text" class="form-control price-field text-end" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control current-stock text-center" readonly tabindex="-1"></td>
        <td>
            <input type="number" step="1" name="items[INDEX][actual_stock]" class="form-control actual-stock text-center" required oninput="calculateRow(this)">
        </td>
        <td><input type="text" class="form-control variance-qty text-center fw-bold" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control variance-amount amount-field text-end fw-bold" readonly tabindex="-1"></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-item" tabindex="-1">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pass items data from Laravel
    const itemsData = @json($items);
    let itemIndex = 0;
    const itemsTableBody = document.getElementById('itemsTableBody');
    const itemRowTemplate = document.getElementById('itemRowTemplate');

    // Add first row on page load
    addItemRow();

    // --- KEY FEATURE: Add new row on TAB press ---
    itemsTableBody.addEventListener('keydown', function(e) {
        // Check if the pressed key is TAB and it's on an 'actual-stock' input field
        if (e.key === 'Tab' && !e.shiftKey && e.target.classList.contains('actual-stock')) {
            const rows = Array.from(itemsTableBody.querySelectorAll('.item-row'));
            const currentRow = e.target.closest('.item-row');
            const isLastRow = rows[rows.length - 1] === currentRow;

            // If it's the last row, prevent default tab behavior and add a new row
            if (isLastRow) {
                e.preventDefault();
                addItemRow();

                // Focus on the item select of the newly created row
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
    });

    // --- Function to Add a New Row ---
    function addItemRow() {
        const template = itemRowTemplate.content.cloneNode(true);
        const row = template.querySelector('.item-row');

        // Replace INDEX placeholder with unique index for form submission
        row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);

        // Populate the Item Select dropdown
        const itemSelect = row.querySelector('.item-select');
        let optionsHtml = '<option value="">Select Item</option>';
        itemsData.forEach(item => {
             // Using 'item_name' and 'item_code' from your model
            optionsHtml += `<option value="${item.id}">${item.item_name} (${item.item_code})</option>`;
        });
        itemSelect.innerHTML = optionsHtml;

        // Add event listener for the remove button
        const removeBtn = row.querySelector('.remove-item');
        removeBtn.addEventListener('click', function() {
             // Allow removing rows, but maybe keep at least one if needed (optional)
            row.remove();
            calculateGrandTotal();
        });

        itemsTableBody.appendChild(row);
        itemIndex++;
    }

    // --- HELPER: Get Stock for Selected Branch ---
    window.getStockForBranch = function(itemId) {
        const branchId = document.getElementById('branch_select').value;
        if (!branchId || !itemId) return 0;

        const item = itemsData.find(i => i.id == itemId);
        if (!item || !item.inventory) return 0;

        const invRecord = item.inventory.find(inv => inv.branch_id == branchId);
        // Note: Using 'current_stock' as identified in previous correction
        return invRecord ? parseInt(invRecord.current_stock) : 0;
    }

    // --- HELPER: Get Price for Selected Branch ---
    window.getPriceForBranch = function(itemId) {
        const branchId = document.getElementById('branch_select').value;
        if (!branchId || !itemId) return 0;

        const item = itemsData.find(i => i.id == itemId);
        if (!item) return 0;

        let price = 0;
        // 1. Try branch specific price
        if (item.branch_prices && item.branch_prices.length > 0) {
            const bp = item.branch_prices.find(p => p.branch_id == branchId);
            if (bp) {
                price = parseFloat(bp.price);
            }
        }
        // 2. Fallback to generic price accessor if branch price is 0 or not found
        if (price === 0 && item.price) {
            price = parseFloat(item.price);
        }
        return price;
    }

    // Update details when an item is selected
    window.updateItemDetails = function(selectElement) {
        const row = selectElement.closest('.item-row');
        const itemId = selectElement.value;

        const stock = getStockForBranch(itemId);
        const price = getPriceForBranch(itemId);

        row.querySelector('.current-stock').value = stock;
        row.querySelector('.price-field').value = price.toFixed(2);

        // Recalculate the row based on new system stock/price
        calculateRow(row.querySelector('.actual-stock'));
    }

    // Handle Branch Change Event
    window.onBranchChange = function() {
        const branchId = document.getElementById('branch_select').value;
        // Find all item selects that have a value selected
        const selects = document.querySelectorAll('.item-select');
        selects.forEach((select) => {
            if(select.value) {
                // Trigger update for existing selections to fetch new branch stock/price
                updateItemDetails(select);
            }
        });

        // Optional: Warn if no branch selected
        if(!branchId) {
             alert('Please select a branch to retrieve correct stock and prices.');
        }
    }

    // Row Calculation Logic
    window.calculateRow = function(inputElement) {
        const row = inputElement.closest('.item-row');

        let current = parseInt(row.querySelector('.current-stock').value) || 0;
        let actual = parseInt(row.querySelector('.actual-stock').value) || 0;
        let price  = parseFloat(row.querySelector('.price-field').value) || 0;

        let varianceQty = actual - current;
        const varianceQtyField = row.querySelector('.variance-qty');
        varianceQtyField.value = varianceQty;

        let varianceAmount = varianceQty * price;
        let amountField = row.querySelector('.variance-amount');
        amountField.value = varianceAmount.toFixed(2);

        // Color Coding
        setVarianceColor(varianceQtyField, varianceQty);
        setVarianceColor(amountField, varianceAmount);

        calculateGrandTotal();
    }

    // Helper for color coding
    function setVarianceColor(element, value) {
        element.classList.remove('text-danger', 'text-success');
        if(value < 0) {
            element.classList.add('text-danger');
        } else if(value > 0) {
            element.classList.add('text-success');
        }
    }

    // Grand Total Calculation
    window.calculateGrandTotal = function() {
        let total = 0;
        const amountInputs = document.querySelectorAll('.amount-field');
        amountInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalField = document.getElementById('grand_total_amount');
        totalField.value = total.toFixed(2);
        setVarianceColor(totalField, total);
    }
});
</script>

<style>
    .table th {
        vertical-align: middle;
        font-weight: 600;
    }
    .item-row td {
        vertical-align: middle;
        padding: 0.5rem;
    }
    /* Make readonly inputs look cleaner in table */
    .item-row .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    /* Ensure inputs fit well in cells */
    .table input.form-control, .table select.form-select {
        min-width: 80px;
    }
</style>
@endsection
