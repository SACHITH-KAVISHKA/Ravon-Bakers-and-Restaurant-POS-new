<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Edit Customer: {{ $customer->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">VAT Number</label>
                            <input type="text" name="vat_no" class="form-control" value="{{ $customer->vat_no }}">
                        </div>
                        <button type="submit" class="btn btn-success">Update Customer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
