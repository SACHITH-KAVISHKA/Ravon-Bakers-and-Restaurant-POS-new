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
                <a href="{{ route('supervisor.productions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Items Added (only) -->
    <div class="row">
        <div class="col-12">

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th class="border-0 px-3 py-3" style="width: 50%;">Item</th>
                                <th class="border-0 px-3 py-3">Item Code</th>
                                <th class="border-0 px-3 py-3 text-center" style="background-color: #e3f2fd;">Quantity Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryRequest->inventoryRequestItems as $iri)
                            <tr class="border-bottom">
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
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- (Duplicate items table removed) -->
</div>
@endsection