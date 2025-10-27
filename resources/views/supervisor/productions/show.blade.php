@extends('layouts.app')

@section('title', 'Production Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
                    <i class="bi bi-clipboard-check" style="color: #667eea;"></i> Production Details
                </h1>
                <div>
                    <a href="{{ route('supervisor.productions.export-details', $inventoryRequest) }}" 
                       class="btn btn-success btn-sm me-2">
                        <i class="bi bi-file-earmark-excel"></i> Export to Excel
                    </a>
                    <a href="{{ route('supervisor.productions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Production Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="text-muted small">Date & Time</label>
                                <div class="fw-bold" style="color: #667eea;">
                                    {{ $inventoryRequest->date_time->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $inventoryRequest->date_time->format('h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="text-muted small">Department</label>
                                <div>
                                    <span class="badge bg-light text-dark">
                                        {{ $inventoryRequest->department ? $inventoryRequest->department->name : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="text-muted small">Total Items</label>
                                <div class="fw-bold" style="color: #667eea; font-size: 1.3rem;">
                                    {{ $inventoryRequest->inventoryRequestItems->count() }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="text-muted small">Total Quantity</label>
                                <div class="fw-bold" style="color: #667eea; font-size: 1.3rem;">
                                    {{ $inventoryRequest->inventoryRequestItems->sum('quantity') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="text-muted small">Status</label>
                                <div>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> {{ ucfirst($inventoryRequest->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if($inventoryRequest->notes)
                        <div class="col-12">
                            <div class="mb-0">
                                <label class="text-muted small">Notes</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $inventoryRequest->notes }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Added (only) -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Items Added to Main Stock</h5>
                </div>
                <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th class="border-0 px-3 py-3" style="width: 5%;">#</th>
                                <th class="border-0 px-3 py-3" style="width: 50%;">Item</th>
                                <th class="border-0 px-3 py-3">Item Code</th>
                                <th class="border-0 px-3 py-3 text-center" style="background-color: #e3f2fd;">Quantity Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryRequest->inventoryRequestItems as $index => $iri)
                            <tr class="border-bottom">
                                <td class="px-3 py-2">{{ $index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <span class="fw-bold" style="color: #667eea;">
                                        {{ $iri->item ? $iri->item->item_name : 'Deleted item' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="badge bg-light text-dark">
                                        {{ $iri->item ? $iri->item->item_code : '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center" style="background-color: #f5f9ff;">
                                    <span class="fw-bold" style="color: #1976d2; font-size: 1.1rem;">
                                        {{ $iri->quantity }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                            <!-- Total Row -->
                            <tr style="background-color: #fff9c4;">
                                <td colspan="3" class="px-3 py-3 text-end fw-bold">TOTAL QUANTITY:</td>
                                <td class="px-3 py-3 text-center fw-bold" style="color: #667eea; font-size: 1.2rem;">
                                    {{ $inventoryRequest->inventoryRequestItems->sum('quantity') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection