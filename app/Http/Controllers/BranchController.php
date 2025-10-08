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
    public function index()
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized access to branch management.');
        }
        
        $branches = Branch::withCount('users')->orderBy('name')->get();
        return view('branches.index', compact('branches'));
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
            ]);

            $branch = Branch::create([
                'name' => $request->name,
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
            'status' => 'required|boolean',
        ]);

        $branch->update($request->only(['name', 'status']));

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
