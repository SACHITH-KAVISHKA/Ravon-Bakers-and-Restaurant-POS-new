<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Prices for {{ $item->item_name }}</h1>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to Items</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Price (LKR)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->branchPrices as $bp)
                        <tr>
                            <td>{{ $bp->branch->name ?? '—' }}</td>
                            <td>LKR {{ number_format($bp->price, 2) }}</td>
                            <td>
                                <form action="{{ route('items.prices.update', [$item, $bp]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="price" value="{{ $bp->price }}">
                                    <button class="btn btn-sm btn-outline-warning" title="Edit">Edit</button>
                                </form>
                                <form action="{{ route('items.prices.destroy', [$item, $bp]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this branch price?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>

            <h6>Add / Update Branch Price</h6>
            <form action="{{ route('items.prices.store', $item) }}" method="POST" class="row g-2 mt-2">
                @csrf
                <div class="col-md-6">
                    <select name="branch_id" class="form-select">
                        <option value="">Select Branch</option>
                        @foreach(App\Models\Branch::orderBy('name')->get() as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">LKR</span>
                        <input type="number" step="0.01" min="0" name="price" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
