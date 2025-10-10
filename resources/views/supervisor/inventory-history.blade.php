@extends('layouts.app')

@section('title', 'Available Stock by Category')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <i class="bi bi-boxes" style="color: #667eea;"></i> Available Stock by Category
                </h1>
                <a href="{{ route('supervisor.add-inventory') }}" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                    <i class="bi bi-plus-circle"></i> Add Inventory
                </a>
            </div>
        </div>
    </div>

    @if($itemsByCategory->count() > 0)
        @foreach($itemsByCategory as $category => $categoryData)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-tag-fill"></i>
                                {{ ucfirst($categoryData['category_name']) }}
                                <span class="badge bg-light text-dark ms-2">{{ $categoryData['total_items'] }} items</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead style="background-color: #f8f9fa;">
                                        <tr>
                                            <th class="border-0 px-4 py-3">Item Name</th>
                                            <th class="border-0 px-4 py-3">Item Code</th>
                                            <th class="border-0 px-4 py-3 text-center">Stock</th>
                                            <th class="border-0 px-4 py-3 text-center">Price</th>
                                            <th class="border-0 px-4 py-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categoryData['items'] as $item)
                                            <tr class="border-bottom">
                                                <td class="px-4 py-3">
                                                    <span class="fw-bold" style="color: #667eea;">{{ $item['name'] }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <code class="text-muted">{{ $item['item_code'] }}</code>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="badge fs-6 px-3 py-2" style="background-color: #e3f2fd; color: #1976d2;">
                                                        {{ $item['current_stock'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="badge fs-6 px-3 py-2" style="background-color: #e8f5e8; color: #2e7d32;">
                                                        ${{ number_format($item['price'], 2) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if($item['is_low_stock'])
                                                        <span class="badge bg-warning text-dark px-3 py-2">
                                                            <i class="bi bi-exclamation-triangle"></i> Low Stock
                                                        </span>
                                                    @elseif($item['current_stock'] > 50)
                                                        <span class="badge px-3 py-2" style="background-color: #c8e6c9; color: #2e7d32;">
                                                            <i class="bi bi-check-circle"></i> Well Stocked
                                                        </span>
                                                    @else
                                                        <span class="badge px-3 py-2" style="background-color: #bbdefb; color: #1976d2;">
                                                            <i class="bi bi-info-circle"></i> Good Stock
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No Stock Available</h4>
                    <p class="text-muted">There are currently no items with available stock.</p>
                    <a href="{{ route('supervisor.add-inventory') }}" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                        <i class="bi bi-plus-circle"></i> Add Inventory
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05) !important;
    transform: translateY(-1px);
    transition: all 0.2s ease-in-out;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15) !important;
}

.badge {
    font-weight: 500;
    letter-spacing: 0.5px;
}

code {
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.9em;
}

th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.btn:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
</style>
@endsection
