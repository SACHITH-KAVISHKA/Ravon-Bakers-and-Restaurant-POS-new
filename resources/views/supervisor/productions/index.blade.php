@extends('layouts.app')

@section('title', 'View Production')

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
                    <i class="bi bi-clipboard-data" style="color: #667eea;"></i> View Production Records
                </h1>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
                    <a href="{{ route('supervisor.productions.export', request()->query()) }}" 
                       class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Export to Excel
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('supervisor.productions.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label fw-bold">
                                <i class="bi bi-calendar3"></i> Date From
                            </label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label fw-bold">
                                <i class="bi bi-calendar3"></i> Date To
                            </label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('supervisor.productions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Productions List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Production Records</h5>
                </div>
                <div class="card-body">
                    @if($productions->count())
                    <!-- Summary Info -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total Productions</small>
                                <h4 class="mb-0" style="color: #667eea;">{{ $productions->total() }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total Items</small>
                                <h4 class="mb-0" style="color: #667eea;">{{ $productions->sum(function($p) { return $p->inventoryRequestItems->count(); }) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total Quantity</small>
                                <h4 class="mb-0" style="color: #667eea;">{{ $productions->sum(function($p) { return $p->inventoryRequestItems->sum('quantity'); }) }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th class="border-0 px-3 py-3">Date & Time</th>
                                    <th class="border-0 px-3 py-3">Department</th>
                                    <th class="border-0 px-3 py-3 text-center">Items Count</th>
                                    <th class="border-0 px-3 py-3 text-center">Total Quantity</th>
                                    <th class="border-0 px-3 py-3 text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productions as $p)
                                <tr class="border-bottom">
                                    <td class="px-3 py-2">
                                        <span class="fw-bold" style="color: #667eea;">{{ $p->date_time->format('M d, Y') }}</span>
                                        <br><small class="text-muted">{{ $p->date_time->format('h:i A') }}</small>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="badge bg-light text-dark">
                                            {{ $p->department ? $p->department->name : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="badge" style="background-color: #e3f2fd; color: #1976d2;">
                                            {{ $p->inventoryRequestItems->count() }} items
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="fw-bold" style="color: #667eea;">{{ $p->inventoryRequestItems->sum('quantity') }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <a href="{{ route('supervisor.productions.show', $p) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $productions->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="text-muted mt-3">No Production Records Found</h5>
                        <p class="text-muted">Production records added to main stock will appear here.</p>
                        <a href="{{ route('supervisor.add-inventory') }}" class="btn btn-primary mt-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="bi bi-plus-circle"></i> Add Production
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection