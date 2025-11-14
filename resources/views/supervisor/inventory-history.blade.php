@extends('layouts.app')

@section('title', 'Inventory Stock by Branch')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                    <i class="bi bi-boxes" style="color: #667eea;"></i> Inventory Stock by Branch
                </h1>
            </div>
        </div>
    </div>

    <!-- Search Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('supervisor.inventory-history') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-bold">
                                <i class="bi bi-calendar3"></i> Filter by Date
                            </label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ $filterDate ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label for="time" class="form-label fw-bold">
                                <i class="bi bi-clock"></i> Filter by Time (From)
                            </label>
                            <input type="time" class="form-control" id="time" name="time" value="{{ $filterTime ?? '' }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="{{ route('supervisor.inventory-history') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                            <a href="{{ route('supervisor.inventory-history.export', ['date' => $filterDate ?? null, 'time' => $filterTime ?? null]) }}" class="btn btn-success ms-2" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); border: none; color: #fff;">
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if($allItems->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-box-seam"></i> 
                            @if($filterDate || $filterTime)
                                Historical Stock Transactions
                            @else
                                Current Inventory Stock
                            @endif
                            <span class="badge bg-light text-dark ms-2">{{ $allItems->count() }} items</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 inventory-table">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th class="border-0 px-3 py-3">Item</th>
                                        <th class="border-0 px-3 py-3 text-center" style="background-color: #e3f2fd; color: #1976d2;">
                                            Main Stock
                                            @if($filterDate || $filterTime)
                                                <br><small class="fw-normal">(Production)</small>
                                            @endif
                                        </th>
                                        @foreach($otherBranches as $branch)
                                            <th class="border-0 px-3 py-3 text-center" style="background-color: #fff3e0; color: #e65100;">
                                                {{ $branch->name }}
                                                @if($filterDate || $filterTime)
                                                    <br><small class="fw-normal">(Transfers)</small>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allItems as $item)
                                        <tr class="border-bottom">
                                            <td class="px-2 py-2">
                                                <div>
                                                    <span class="fw-bold d-block" style="color: #667eea; font-size:0.95rem;">{{ $item['name'] }}</span>
                                                    <small class="text-muted" style="font-size:0.8rem;">{{ $item['item_code'] }}</small>
                                                </div>
                                            </td>
                                            <td class="px-2 py-2 text-center" style="background-color: #f5f9ff;">
                                                <span class="stock-number" style="color: {{ $item['main_stock'] < 0 ? '#dc3545' : '#0b66d1' }}; font-weight:700; font-size:0.95rem;">{{ $item['main_stock'] }}</span>
                                            </td>
                                            @foreach($otherBranches as $branch)
                                                <td class="px-2 py-2 text-center" style="background-color: #fffbf5;">
                                                    @php $qty = $item['branch_stocks'][$branch->name] ?? 0; @endphp
                                                    <span class="stock-number" style="color: {{ $qty < 0 ? '#dc3545' : ($qty > 0 ? '#e65100' : '#6c757d') }}; font-weight:{{ $qty != 0 ? '600' : '400' }}; font-size:0.9rem;">{{ $qty }}</span>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $allItems->links() }}
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No Stock Available</h4>
                    <p class="text-muted">There are currently no items with available stock.</p>
                    <a href="{{ route('supervisor.add-inventory') }}" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                        <i class="bi bi-plus-circle"></i> Add Production
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.inventory-table {
    font-size: 0.95rem;
}

.inventory-table thead th {
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    white-space: nowrap;
    border-bottom: 2px solid #dee2e6 !important;
}

.inventory-table tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.03) !important;
    transition: all 0.2s ease-in-out;
}

.inventory-table tbody td {
    vertical-align: middle;
}

/* Compact adjustments */
.inventory-table tbody td, .inventory-table thead th {
    padding-top: 0.35rem !important;
    padding-bottom: 0.35rem !important;
}

.inventory-table .badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15) !important;
}

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
    display: inline-block;
}

code {
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.85em;
}

.btn:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.form-label {
    color: #495057;
    font-size: 0.9rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Responsive table adjustments */
@media (max-width: 768px) {
    .inventory-table {
        font-size: 0.85rem;
    }
    
    .inventory-table thead th {
        padding: 0.5rem !important;
        font-size: 0.75rem;
    }
    
    .inventory-table tbody td {
        padding: 0.5rem !important;
    }
    
    .badge {
        font-size: 0.75rem !important;
        padding: 0.25rem 0.5rem !important;
    }
}
</style>
@endsection
