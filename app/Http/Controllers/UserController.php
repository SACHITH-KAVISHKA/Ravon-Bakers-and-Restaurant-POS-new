<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user has permission
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to user management.');
        }
        
        $users = User::with('branch')->orderBy('created_at', 'desc')->paginate(15);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to user management.');
        }
        
        $branches = Branch::active()->orderBy('name')->get();
        return view('users.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to user management.');
        }
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,staff,supervisor']
        ];

        // Add branch validation only for staff members
        if ($request->role === 'staff') {
            $rules['branch_id'] = ['required', 'exists:branches,id'];
        }

        $request->validate($rules);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ];

        // Add branch_id only for staff members
        if ($request->role === 'staff' && $request->branch_id) {
            $userData['branch_id'] = $request->branch_id;
        }

        User::create($userData);

        return redirect()->route('users.index')
                        ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to user management.');
        }
        
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to user management.');
        }
        
        $branches = Branch::active()->orderBy('name')->get();
        return view('users.edit', compact('user', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to user management.');
        }
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:admin,staff,supervisor'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        // Add branch validation only for staff members
        if ($request->role === 'staff') {
            $rules['branch_id'] = ['required', 'exists:branches,id'];
        }

        $request->validate($rules);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Add or remove branch_id based on role
        if ($request->role === 'staff' && $request->branch_id) {
            $updateData['branch_id'] = $request->branch_id;
        } elseif ($request->role !== 'staff') {
            $updateData['branch_id'] = null;
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
                        ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to user management.');
        }
        
        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                            ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'User deleted successfully.');
    }
}
