<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     if (!Gate::allows('manage-users')) {
    //         abort(403, 'Unauthorized access to branch management.');
    //     }

    //     $branches = Branch::withCount('users')->orderBy('name')->get();
    //     return view('branches.index', compact('branches'));
    // }

    public function index()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $name = str_replace(' ', '', strtolower($user->name ?? ''));
        $username = str_replace(' ', '', strtolower($user->username ?? ''));

        $isHolding = ($role === 'holding' || str_contains($name, 'adminh') || str_contains($username, 'adminh'));
        $isDelight = ($role === 'delight' || str_contains($name, 'admind') || str_contains($username, 'admind'));
        $isAdminOrDirector = in_array($role, ['admin', 'director']);

        // Restrict access to branch management for non-admin/director users
        if (!$isAdminOrDirector && !$isHolding && !$isDelight) {
            abort(403, 'Unauthorized access to branch management.');
        }

        $query = Branch::withCount('users')->orderBy('name');

        // Admin H  restricted to Branch IDs: 6, 7
        if ($isHolding) {
            $query->whereIn('id', [6, 7]);
        }
        // Admin D restricted to Branch IDs: 2, 3, 4, 5
        elseif ($isDelight) {
            $query->whereIn('id', [2, 3, 4, 5]);
        }

        $branches = $query->get();

        // Manage Add/Edit/Delete permissions only for Admin and Director
        $canManageBranches = $isAdminOrDirector;

        return view('branches.index', compact('branches', 'canManageBranches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-users')) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to branch management.'
                ], 403);
            }
            abort(403, 'Unauthorized access to branch management.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:branches,name',
                'company_name' => 'nullable|string|max:255',
                'display_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'telephone' => 'nullable|string|max:20',
            ]);

            $branch = Branch::create([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'display_name' => $request->display_name,
                'address' => $request->address,
                'telephone' => $request->telephone,
                'status' => 1,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'branch' => $branch,
                    'message' => 'Branch created successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Branch created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the branch.'
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while creating the branch.');
        }
    }

    /**
     * Get all active branches for AJAX requests.
     */
    public function getActiveBranches()
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access.');
        }

        $branches = Branch::active()->orderBy('name')->get();
        return response()->json($branches);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to branch management.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'company_name' => 'nullable|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:20',
            'status' => 'required|boolean',
        ]);

        $branch->update($request->only(['name', 'company_name', 'display_name', 'address', 'telephone', 'status']));

        return redirect()->back()->with('success', 'Branch updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to branch management.');
        }

        // Check if branch has users
        if ($branch->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete branch with assigned users.');
        }

        $branch->delete();
        return redirect()->back()->with('success', 'Branch deleted successfully!');
    }
}
