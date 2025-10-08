@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New User</h5>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-light btn-sm">
                        Back to Users
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <!-- Name Field -->
                    <div class="row mb-4">
                        <label for="name" class="col-md-3 col-form-label text-md-end fw-semibold">
                            Full Name
                        </label>
                        <div class="col-md-7">
                            <input id="name" type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" 
                                   required autocomplete="name" autofocus
                                   placeholder="Enter user's full name">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="row mb-4">
                        <label for="email" class="col-md-3 col-form-label text-md-end fw-semibold">
                            Email Address
                        </label>
                        <div class="col-md-7">
                            <input id="email" type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" 
                                   required autocomplete="email"
                                   placeholder="Enter email address">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Role Field -->
                    <div class="row mb-4">
                        <label for="role" class="col-md-3 col-form-label text-md-end fw-semibold">
                            User Role
                        </label>
                        <div class="col-md-7">
                            <select id="role" class="form-select @error('role') is-invalid @enderror" 
                                    name="role" required onchange="toggleBranchField()">
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                                    Administrator
                                </option>
                                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>
                                    Staff Member
                                </option>
                                <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>
                                    Supervisor
                                </option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                           
                        </div>
                    </div>

                    <!-- Branch Field (Hidden by default, shown for staff) -->
                    <div class="row mb-4" id="branchField" style="display: none;">
                        <label for="branch_id" class="col-md-3 col-form-label text-md-end fw-semibold">
                            Branch
                        </label>
                        <div class="col-md-7">
                            <select id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" 
                                    name="branch_id">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>                    <!-- Password Field -->
                    <div class="row mb-4">
                        <label for="password" class="col-md-3 col-form-label text-md-end fw-semibold">
                            Password
                        </label>
                        <div class="col-md-7">
                            <input id="password" type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="new-password"
                                   placeholder="Enter password (minimum 8 characters)">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="row mb-4">
                        <label for="password-confirm" class="col-md-3 col-form-label text-md-end fw-semibold">
                            Confirm Password
                        </label>
                        <div class="col-md-7">
                            <input id="password-confirm" type="password" 
                                   class="form-control" name="password_confirmation" 
                                   required autocomplete="new-password"
                                   placeholder="Confirm password">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row mb-0">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-lg"></i> Create User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle branch field based on role selection
    function toggleBranchField() {
        const roleSelect = document.getElementById('role');
        const branchField = document.getElementById('branchField');
        const branchSelect = document.getElementById('branch_id');
        
        if (roleSelect.value === 'staff') {
            branchField.style.display = 'flex';
            branchSelect.setAttribute('required', 'required');
        } else {
            branchField.style.display = 'none';
            branchSelect.removeAttribute('required');
            branchSelect.value = '';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleBranchField();
    });
</script>

<style>
    .card {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        padding: 1.5rem 2rem;
    }
    
    .card-body {
        background: #fafbfc;
        padding: 2rem !important;
    }
    
    .col-form-label {
        font-size: 0.95rem;
        color: #495057;
        line-height: 1.6;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        background: white;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 0.75rem 2rem;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-outline-secondary {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border: 2px solid #6c757d;
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
    }
    
    .btn-outline-light {
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
    }
    
    .btn-outline-light:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        border-color: rgba(255,255,255,0.5);
    }
    
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #dc3545;
        font-weight: 500;
    }
    
    .form-text {
        color: #6c757d;
        font-size: 0.85rem;
    }
    
    .role-descriptions {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 3px solid #667eea;
    }
    
    .role-descriptions div {
        font-size: 0.85rem;
        line-height: 1.4;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .col-md-3 {
            text-align: left !important;
            margin-bottom: 0.5rem;
        }
        
        .col-md-7 {
            padding-left: 15px;
        }
        
        .offset-md-3 {
            margin-left: 0;
        }
        
        .card-body {
            padding: 1.5rem !important;
        }
        
        .btn-lg {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection