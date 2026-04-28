<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryLog;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('stock', 'asc')->get();
        return view('admin.inventory.index', compact('products'));
    }
    
    public function adjust(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer'
        ]);
        
        $oldStock = $product->stock;
        $newStock = $oldStock + $request->quantity;
        
        if ($newStock < 0) {
            return back()->with('error', 'Stock cannot be negative.');
        }
        
        $product->update(['stock' => $newStock]);
        
        InventoryLog::create([
            'product_id' => $product->id,
            'quantity_change' => $request->quantity,
            'old_quantity' => $oldStock,
            'new_quantity' => $newStock,
            'reason' => 'admin_adjustment',
            'reference_id' => auth()->id()
        ]);
        
        $message = $request->quantity > 0 
            ? "Added {$request->quantity} units to {$product->name}"
            : "Removed " . abs($request->quantity) . " units from {$product->name}";
        
        return back()->with('success', $message);
    }
}