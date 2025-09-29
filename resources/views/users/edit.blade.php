@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Edit User: {{ $user->name }}
                    </h5>
                    <div>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info btn-sm me-2">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Users
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <!-- Name Field -->
                    <div class="row mb-3">
                        <label for="name" class="col-md-3 col-form-label text-md-end">
                            Full Name
                        </label>
                        <div class="col-md-9">
                            <input id="name" type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $user->name) }}" 
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
                    <div class="row mb-3">
                        <label for="email" class="col-md-3 col-form-label text-md-end">
                            Email Address
                        </label>
                        <div class="col-md-9">
                            <input id="email" type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email', $user->email) }}" 
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
                    <div class="row mb-3">
                        <label for="role" class="col-md-3 col-form-label text-md-end">
                            User Role
                        </label>
                        <div class="col-md-9">
                            <select id="role" class="form-select @error('role') is-invalid @enderror" 
                                    name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                    Administrator
                                </option>
                                <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>
                                    Staff Member
                                </option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3">
                        Change Password (Optional)
                    </h6>

                    <!-- Password Field -->
                    <div class="row mb-3">
                        <label for="password" class="col-md-3 col-form-label text-md-end">
                            New Password
                        </label>
                        <div class="col-md-9">
                            <input id="password" type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" autocomplete="new-password"
                                   placeholder="Leave blank to keep current password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Leave blank if you don't want to change the password</small>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="row mb-3">
                        <label for="password-confirm" class="col-md-3 col-form-label text-md-end">
                            Confirm New Password
                        </label>
                        <div class="col-md-9">
                            <input id="password-confirm" type="password" 
                                   class="form-control" name="password_confirmation" 
                                   autocomplete="new-password"
                                   placeholder="Confirm new password">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row mb-0">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-check-lg"></i> Update User
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
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
</style>
@endsection