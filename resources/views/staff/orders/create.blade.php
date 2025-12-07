@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-dark">Create New Order</h1>
            <a href="{{ route('staff.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-cart-plus"></i> Order Details
            </div>
            <div class="card-body">

                <form action="{{ route('staff.orders.store') }}" method="POST">
                    @csrf
                <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_time" class="form-label">Collect Date & Time</label>
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

                        <div class="col-md-4 mb-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select @error('branch_id') is-invalid @enderror"
                                    id="branch_id"
                                    name="branch_id"
                                    required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ (old('branch_id') == $branch->id || (Auth::user()->branch_id == $branch->id)) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text"
                                class="form-control @error('customer_name') is-invalid @enderror"
                                id="customer_name"
                                name="customer_name"
                                value="{{ old('customer_name') }}"
                                placeholder="Enter customer name"
                                required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-semibold mb-0">
                                    <i class="bi bi-basket"></i> Order Items
                                </h5>
                                <small class="text-muted">Tip: Press <b>Tab</b> on the "Qty" field to add a new row.</small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="itemsTable">
                                    <thead class="table-primary text-white">
                                        <tr>
                                            <th width="40%">Item</th>
                                            <th width="15%">Price</th>
                                            <th width="15%">Qty</th>
                                            <th width="20%">Subtotal</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light fw-bold">
                                            <td colspan="3" class="text-end align-middle">Total Amount:</td>
                                            <td>
                                                <input type="text" id="grand_total" class="form-control fw-bold" value="0.00" readonly tabindex="-1">
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="bi bi-chat-text"></i> Notes</label>
                            <textarea class="form-control" name="notes" rows="4" placeholder="Optional notes..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 p-3">
                                <div class="mb-3 row">
                                    <label class="col-sm-4 col-form-label fw-semibold">Payment Method</label>
                                    <div class="col-sm-8">
                                        <select class="form-select" name="payment_method" required>
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="Cash & Card">Cash & Card</option>
                                            <option value="online">Online Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label class="col-sm-4 col-form-label fw-bold text-primary">Paid Amount</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" class="form-control" name="paid_amount" id="paid_amount" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4 col-form-label fw-bold text-danger">Balance</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="text" class="form-control text-danger fw-bold" id="balance_amount" readonly value="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Order
                        </button>
                    </div>
                </form> </div>
        </div>
    </div>
</div>

<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[INDEX][item_id]" class="form-select item-select" required onchange="updateRowPrice(this)">
                <option value="">Select Item</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" data-price="{{ $item->price }}">
                    {{ $item->item_name }} ({{ $item->item_code }})
                </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control price-input text-end" name="items[INDEX][price]" readonly tabindex="-1">
        </td>
        <td>
            <input type="number" class="form-control qty-input text-center" name="items[INDEX][quantity]" step="0.01" min="0.01" required oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" class="form-control subtotal-input text-end fw-bold" readonly tabindex="-1">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-item" tabindex="-1">
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

        // Add first row on load
        addItemRow();

        // Key Feature: Tab Key Logic
        itemsTableBody.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' && !e.shiftKey && e.target.classList.contains('qty-input')) {
                const rows = Array.from(itemsTableBody.querySelectorAll('.item-row'));
                const currentRow = e.target.closest('.item-row');
                const isLastRow = rows[rows.length - 1] === currentRow;

                if (isLastRow) {
                    e.preventDefault();
                    addItemRow();
                    setTimeout(() => {
                        const newRows = itemsTableBody.querySelectorAll('.item-row');
                        const newRow = newRows[newRows.length - 1];
                        newRow.querySelector('.item-select').focus();
                    }, 10);
                }
            }
        });

        // Add Row Function
        function addItemRow() {
            const template = itemRowTemplate.content.cloneNode(true);
            const row = template.querySelector('.item-row');

            // Replace INDEX with unique ID
            row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);

            // Remove button logic
            row.querySelector('.remove-item').addEventListener('click', function() {
                if(itemsTableBody.children.length > 1) {
                    row.remove();
                    calculateGrandTotal();
                } else {
                    alert('Order must have at least one item.');
                }
            });

            itemsTableBody.appendChild(row);
            itemIndex++;
        }

        // Global functions
        window.updateRowPrice = function(select) {
            const row = select.closest('tr');
            const price = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 0;
            row.querySelector('.price-input').value = price.toFixed(2);
            calculateRow(row.querySelector('.qty-input'));
        }

        window.calculateRow = function(input) {
            const row = input.closest('tr');
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const subtotal = price * qty;

            row.querySelector('.subtotal-input').value = subtotal.toFixed(2);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal-input').forEach(inp => {
                total += parseFloat(inp.value) || 0;
            });
            document.getElementById('grand_total').value = total.toFixed(2);
            calculateBalance();
        }

        function calculateBalance() {
            const total = parseFloat(document.getElementById('grand_total').value) || 0;
            const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
            const balance = total - paid;
            document.getElementById('balance_amount').value = balance.toFixed(2);
        }

        document.getElementById('paid_amount').addEventListener('input', calculateBalance);
    });
</script>
@endsection
