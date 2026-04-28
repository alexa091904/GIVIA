<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $products = $query->paginate(15);
        return view('admin.products.index', compact('products'));
    }
    
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048'
        ]);
        
        $data = $request->except('image');
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_url'] = Storage::url($path);
        }
        
        $product = Product::create($data);
        
        InventoryLog::create([
            'product_id' => $product->id,
            'quantity_change' => $product->stock,
            'old_quantity' => 0,
            'new_quantity' => $product->stock,
            'reason' => 'initial_stock'
        ]);
        
        return redirect()->route('admin.products.index')
                        ->with('success', 'Product created successfully');
    }
    
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }
    
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048'
        ]);
        
        $oldStock = $product->stock;
        
        if ($request->hasFile('image')) {
            if ($product->image_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image_url));
            }
            $path = $request->file('image')->store('products', 'public');
            $request->merge(['image_url' => Storage::url($path)]);
        }
        
        $product->update($request->all());
        
        // Log stock change if different
        if ($oldStock != $product->stock) {
            InventoryLog::create([
                'product_id' => $product->id,
                'quantity_change' => $product->stock - $oldStock,
                'old_quantity' => $oldStock,
                'new_quantity' => $product->stock,
                'reason' => 'manual_update'
            ]);
        }
        
        return redirect()->route('admin.products.index')
                        ->with('success', 'Product updated successfully');
    }
    
    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Cannot delete product with existing orders');
        }
        
        $product->delete();
        return redirect()->route('admin.products.index')
                        ->with('success', 'Product deleted successfully');
    }
}