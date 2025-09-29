@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> User Profile: {{ $user->name }}
                    </h5>
                    <div>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-warning btn-sm me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Users
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- User Avatar Section -->
                    <div class="col-md-4 text-center">
                        <div class="user-avatar mb-3">
                            <i class="bi bi-person-circle display-1 text-primary"></i>
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <div class="role-badge mb-3">
                            @if($user->role === 'admin')
                                <span class="badge bg-danger fs-6">
                                    <i class="bi bi-shield-fill-check"></i> Administrator
                                </span>
                            @else
                                <span class="badge bg-primary fs-6">
                                    <i class="bi bi-person-badge"></i> Staff Member
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- User Information Section -->
                    <div class="col-md-8">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-info-circle"></i> User Information
                        </h6>
                        
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 30%;">
                                        <i class="bi bi-hash"></i> User ID:
                                    </td>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">
                                        <i class="bi bi-person"></i> Full Name:
                                    </td>
                                    <td><strong>{{ $user->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">
                                        <i class="bi bi-envelope"></i> Email:
                                    </td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">
                                        <i class="bi bi-shield-check"></i> Role:
                                    </td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">Administrator</span>
                                        @else
                                            <span class="badge bg-primary">Staff Member</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">
                                        <i class="bi bi-calendar-plus"></i> Created:
                                    </td>
                                    <td>{{ $user->created_at->format('F d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">
                                        <i class="bi bi-calendar-check"></i> Last Updated:
                                    </td>
                                    <td>{{ $user->updated_at->format('F d, Y \a\t g:i A') }}</td>
                                </tr>
                                @if($user->email_verified_at)
                                <tr>
                                    <td class="text-muted">
                                        <i class="bi bi-check-circle"></i> Email Verified:
                                    </td>
                                    <td>
                                        <span class="text-success">
                                            <i class="bi bi-check-circle-fill"></i>
                                            {{ $user->email_verified_at->format('F d, Y \a\t g:i A') }}
                                        </span>
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td class="text-muted">
                                        <i class="bi bi-exclamation-circle"></i> Email Verified:
                                    </td>
                                    <td>
                                        <span class="text-warning">
                                            <i class="bi bi-exclamation-circle-fill"></i>
                                            Not verified
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit User
                            </a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                      class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash"></i> Delete User
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
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
    
    .user-avatar i {
        font-size: 5rem;
    }
    
    .role-badge .badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    
    .permission-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0;
    }
    
    .table td {
        padding: 0.5rem 0;
        border: none;
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
        border: none;
        color: white;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
    }
</style>
@endsection