<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Category::class);
        
        $categories = Category::active()->orderBy('name')->get();
        
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Category::class);
        
        $categories = Category::active()->orderBy('name')->get();
        
        return view('categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Category::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Use updateOrCreate to either create new or reactivate existing deleted category
        $category = Category::updateOrCreate(
            ['name' => $validated['name']], // Find by name
            ['status' => 1] // Set status to active
        );
        
        if ($category->wasRecentlyCreated) {
            $message = 'Category created successfully!';
        } else {
            $message = 'Category reactivated successfully!';
        }

        return redirect()->route('categories.index')
                        ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);
        
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $this->authorize('update', $category);
        
        $categories = Category::active()->where('id', '!=', $category->id)->orderBy('name')->get();
        
        return view('categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id . ',id,status,1',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
                        ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        
        $category->softDelete();

        return redirect()->route('categories.index')
                        ->with('success', 'Category deleted successfully!');
    }

    /**
     * Restore a soft deleted category.
     */
    public function restore(Category $category)
    {
        $this->authorize('delete', $category);
        
        $category->restore();

        return redirect()->route('categories.index')
                        ->with('success', 'Category restored successfully!');
    }
}
