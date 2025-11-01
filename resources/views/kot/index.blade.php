@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3><i class="bi bi-receipt"></i> KOT/BOT Order Tracking</h3>
                <div>
                    <span class="text-muted">
                        <i class="bi bi-info-circle"></i> Orders are auto-created from POS sales
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="all" {{ $type == 'all' ? 'selected' : '' }}>All</option>
                        <option value="KOT" {{ $type == 'KOT' ? 'selected' : '' }}>KOT</option>
                        <option value="BOT" {{ $type == 'BOT' ? 'selected' : '' }}>BOT</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order No</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kots as $kot)
                        <tr>
                            <td>
                                <strong>{{ $kot->kot_no }}</strong>
                                @if($kot->type === 'KOT')
                                <span class="badge bg-primary ms-2">Kitchen</span>
                                @else
                                <span class="badge bg-info ms-2">Bar</span>
                                @endif
                            </td>
                            <td>{{ $kot->kotItems->count() }} items</td>
                            <td><strong>LKR {{ number_format($kot->kotItems->sum('total_price'), 2) }}</strong></td>
                            <td>{{ $kot->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('kot.show', $kot) }}" class="btn btn-outline-primary btn-sm" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-2">No orders found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $kots->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
