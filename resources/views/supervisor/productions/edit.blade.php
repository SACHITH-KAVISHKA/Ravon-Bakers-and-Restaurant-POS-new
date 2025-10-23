@extends('layouts.app')

@section('title', 'Edit Production')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                    <i class="bi bi-pencil-square" style="color: #667eea;"></i> Edit Production
                </h1>
                <a href="{{ route('supervisor.productions.show', $inventoryRequest) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('supervisor.productions.update', $inventoryRequest) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Left Column - Production Details -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-info-circle"></i> Production Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="date_time" class="form-label fw-bold">
                                <i class="bi bi-calendar3"></i> Date & Time <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" 
                                   id="date_time" 
                                   name="date_time" 
                                   class="form-control @error('date_time') is-invalid @enderror" 
                                   value="{{ old('date_time', $inventoryRequest->date_time->format('Y-m-d\TH:i')) }}" 
                                   required>
                            @error('date_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold">
                                <i class="bi bi-sticky"></i> Notes
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Add any additional notes...">{{ old('notes', $inventoryRequest->notes) }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> <small>Updating quantities will automatically adjust main inventory stock.</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                            <a href="{{ route('supervisor.productions.show', $inventoryRequest) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Items -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-box-seam"></i> Production Items
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" id="itemsTable">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th class="border-0 px-3 py-3" style="width: 60%;">Item Name</th>
                                        <th class="border-0 px-3 py-3 text-center" style="background-color: #e3f2fd; width: 40%;">
                                            Quantity <span class="text-danger">*</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventoryRequest->inventoryRequestItems as $index => $iri)
                                    <tr class="border-bottom">
                                        <td class="px-3 py-2">
                                            <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $iri->item_id }}">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-box text-muted me-2"></i>
                                                <div>
                                                    <span class="fw-bold" style="color: #667eea;">
                                                        {{ $iri->item ? $iri->item->item_name : 'Deleted item' }}
                                                    </span>
                                                    @if($iri->item)
                                                    <br><small class="text-muted">{{ $iri->item->item_code }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center" style="background-color: #f5f9ff;">
                                            <input type="number" 
                                                   name="items[{{ $index }}][quantity]" 
                                                   class="form-control form-control-sm text-center @error('items.'.$index.'.quantity') is-invalid @enderror" 
                                                   value="{{ old('items.'.$index.'.quantity', $iri->quantity) }}" 
                                                   min="1" 
                                                   required
                                                   style="max-width: 150px; display: inline-block;">
                                            @error('items.'.$index.'.quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
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
    </form>
</div>
@endsection
