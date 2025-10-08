@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-building me-2"></i> Branch Management
        </h1>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
        <i class="bi bi-plus-lg"></i> Add New Branch
    </button>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Branches</h5>
    </div>
    <div class="card-body">
        @if($branches->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                          
                            <th>Branch Name</th>
                            <th>Status</th>
                            <th>Users Count</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                            <tr>
                               
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-building text-muted me-2"></i>
                                        {{ $branch->name }}
                                    </div>
                                </td>
                                <td>
                                    @if($branch->status)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $branch->users_count ?? 0 }} users
                                    </span>
                                </td>
                                <td>{{ $branch->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="editBranch({{ $branch->id }}, '{{ $branch->name }}', {{ $branch->status }})"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($branch->users_count == 0)
                                            <form method="POST" action="{{ route('branches.destroy', $branch) }}" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this branch?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    title="Cannot delete branch with users" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-building display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Branches Found</h4>
                <p class="text-muted">Get started by adding your first branch.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                    <i class="bi bi-plus-lg"></i> Add New Branch
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Add/Edit Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBranchModalLabel">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="branchForm" method="POST" action="{{ route('branches.store') }}">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="">
                <input type="hidden" id="branchId" name="branch_id" value="">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="branch_name" class="form-label">Branch Name</label>
                        <input type="text" class="form-control" id="branch_name" name="name" required 
                               placeholder="Enter branch name">
                        <div class="invalid-feedback" id="branch_name_error"></div>
                    </div>
                    
                    <div class="mb-3" id="statusField" style="display: none;">
                        <label for="branch_status" class="form-label">Status</label>
                        <select class="form-select" id="branch_status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span id="branchSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span id="submitText">Add Branch</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editBranch(id, name, status) {
        const modal = document.getElementById('addBranchModal');
        const form = document.getElementById('branchForm');
        const title = document.getElementById('addBranchModalLabel');
        const submitText = document.getElementById('submitText');
        const statusField = document.getElementById('statusField');
        
        // Change modal title and form
        title.textContent = 'Edit Branch';
        submitText.textContent = 'Update Branch';
        
        // Set form action and method
        form.action = `/branches/${id}`;
        document.getElementById('methodField').value = 'PUT';
        document.getElementById('branchId').value = id;
        
        // Fill form fields
        document.getElementById('branch_name').value = name;
        document.getElementById('branch_status').value = status;
        
        // Show status field for edit
        statusField.style.display = 'block';
        
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    // Reset modal when closed
    document.getElementById('addBranchModal').addEventListener('hidden.bs.modal', function () {
        const form = document.getElementById('branchForm');
        const title = document.getElementById('addBranchModalLabel');
        const submitText = document.getElementById('submitText');
        const statusField = document.getElementById('statusField');
        
        // Reset modal
        title.textContent = 'Add New Branch';
        submitText.textContent = 'Add Branch';
        form.action = '{{ route("branches.store") }}';
        document.getElementById('methodField').value = '';
        document.getElementById('branchId').value = '';
        statusField.style.display = 'none';
        
        // Reset form
        form.reset();
        
        // Clear errors
        document.getElementById('branch_name').classList.remove('is-invalid');
        document.getElementById('branch_name_error').textContent = '';
    });
</script>

<style>
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 10px;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        border-radius: 10px 10px 0 0 !important;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
    
    .table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 500;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
</style>
@endsection