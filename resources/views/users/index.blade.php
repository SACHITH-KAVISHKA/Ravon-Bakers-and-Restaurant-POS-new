@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people-fill me-2"></i> User Management
        </h1>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add New User
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">System Users</h5>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created Date</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle text-muted me-2"></i>
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-fill-check"></i> Admin
                                        </span>
                                    @else
                                        <span class="badge bg-primary">
                                            <i class="bi bi-person-badge"></i> Staff
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>{{ $user->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    title="Cannot delete yourself" disabled>
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

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-people display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Users Found</h4>
                <p class="text-muted">Get started by adding your first user.</p>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Add New User
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    .table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 500;
    }
    
    .btn-group .btn {
        border-radius: 4px !important;
        margin-right: 2px;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }
    
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 10px;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection