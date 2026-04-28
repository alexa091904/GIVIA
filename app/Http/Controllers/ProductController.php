<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);
        
        // SEARCH: Search by name or description
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // FILTER: By category
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }
        
        // FILTER: Price range
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // FILTER: In stock only
        if ($request->has('in_stock') && $request->in_stock) {
            $query->where('stock', '>', 0);
        }
        
        // SORTING: Multiple options
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        
        $allowedSorts = ['name', 'price', 'created_at', 'stock'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order);
        }
        
        // PAGINATION: 12 products per page
        $products = $query->paginate(12);
        
        // Get categories with product counts for filter sidebar
        $categories = Category::withCount('products')->get();
        
        // Get price range for filter
        $priceRange = [
            'min' => Product::min('price') ?? 0,
            'max' => Product::max('price') ?? 1000
        ];
        
        if ($request->wantsJson()) {
            return response()->json([
                'products' => $products,
                'filters' => [
                    'categories' => $categories,
                    'price_range' => $priceRange
                ]
            ]);
        }
        
        return view('products', compact('products', 'categories', 'priceRange'));
    }
    
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        
        // Get related products from same category
        $relatedProducts = Product::where('category_id', $product->category_id)
                                  ->where('id', '!=', $id)
                                  ->where('is_active', true)
                                  ->limit(4)
                                  ->get();
        
        if (request()->wantsJson()) {
            return response()->json([
                'product' => $product,
                'related_products' => $relatedProducts
            ]);
        }
        
        return view('product-detail', compact('product', 'relatedProducts'));
    }
}       