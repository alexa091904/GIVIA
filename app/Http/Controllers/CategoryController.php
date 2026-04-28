<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        }
        
        return view('categories.index', compact('categories'));
    }
    
    /**
     * Display the specified category with its products.
     */
    public function show($id)
    {
        $category = Category::with('products')->findOrFail($id);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        }
        
        return view('categories.show', compact('category'));
    }
    
    /**
     * Store a newly created category (Admin only).
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string'
        ]);
        
        $category = Category::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }
    
    /**
     * Update the specified category (Admin only).
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $category = Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string'
        ]);
        
        $category->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }
    
    /**
     * Remove the specified category (Admin only).
     */
    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $category = Category::findOrFail($id);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete category with existing products'
            ], 400);
        }
        
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}