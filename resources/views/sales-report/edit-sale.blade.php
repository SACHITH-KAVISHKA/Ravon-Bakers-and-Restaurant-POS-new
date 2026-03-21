<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> Edit Sale Information
        </h1>
        <a href="{{ route('sales-report.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Report
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Update Receipt & Customer Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sales-report.update', $sale->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="receipt_no" class="form-label fw-bold">Receipt Number</label>
                            <input type="text" name="receipt_no" id="receipt_no"
                                   class="form-control form-control-lg @error('receipt_no') is-invalid @enderror"
                                   value="{{ old('receipt_no', $sale->receipt_no) }}" required>
                            @error('receipt_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="mb-3 mt-3">
                            <label for="customer_select" class="form-label fw-bold">Select Customer</label>
                            <select name="customer_name" id="customer_select" class="form-select form-select-lg">
                                <option value="">-- Select Registered Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->name }}"
                                            data-vat="{{ $customer->vat_no }}"
                                            {{ old('customer_name', $sale->customer_name) == $customer->name ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="customer_vat" class="form-label fw-bold">Customer VAT Number</label>
                            <input type="text" name="customer_vat" id="customer_vat"
                                   class="form-control" value="{{ old('customer_vat', $sale->customer_vat) }}"
                                   placeholder="VAT number will appear here...">
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i> Update Sale Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customerSelect = document.getElementById('customer_select');
            const vatInput = document.getElementById('customer_vat');

            customerSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const vatNo = selectedOption.getAttribute('data-vat');

                if (this.value !== "") {
                    vatInput.value = vatNo || '';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
