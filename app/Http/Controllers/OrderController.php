<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\InventoryLog;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items.product', 'payment', 'delivery')
                      ->where('user_id', Auth::id())
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }
        
        return view('order-history', compact('orders'));
    }
    
    public function show($id)
    {
        $order = Order::with(['items.product', 'payment', 'delivery'])
                     ->where('user_id', Auth::id())
                     ->findOrFail($id);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        }
        
        return view('order-details', compact('order'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|min:10',
            'billing_address' => 'required|string|min:10',
            'payment_method' => 'required|in:credit_card,paypal,cod',
            'name' => 'nullable|string',
            'email' => 'nullable|email'
        ]);
        
        $cart = Cart::where('user_id', Auth::id())
                   ->orWhere('session_id', session()->getId())
                   ->with('items.product')
                   ->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $totalAmount = 0;
            $orderItems = [];
            
            // Check stock AND calculate total
            foreach ($cart->items as $item) {
                $product = $item->product;
                
                // Check stock availability
                if ($product->stock < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$item->quantity}");
                }
                
                $totalAmount += $item->quantity * $product->price;
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                    'product_name' => $product->name
                ];
            }
            
            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address
            ]);
            
            // Create order items AND deduct stock
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
                
                // Deduct stock from product
                $product = Product::find($item['product_id']);
                $oldStock = $product->stock;
                $product->decrement('stock', $item['quantity']);
                
                // Log inventory change
                InventoryLog::create([
                    'product_id' => $product->id,
                    'quantity_change' => -$item['quantity'],
                    'old_quantity' => $oldStock,
                    'new_quantity' => $product->stock,
                    'reason' => 'order_placed',
                    'reference_id' => $order->id,
                    'notes' => "Order #{$order->order_number} - {$item['quantity']} x {$item['product_name']}"
                ]);
            }
            
            // Create payment record
            $order->payment()->create([
                'amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'transaction_id' => null
            ]);
            
            // Create delivery record - FIXED: Using correct column name
            $order->delivery()->create([
                'status' => 'pending',
                'estimated_delivery' => now()->addDays(5),  // Changed from 'estimated_delivery_date' to 'estimated_delivery'
                'tracking_number' => null,
                'current_location' => 'Processing'
            ]);
            
            // Clear cart
            $cart->items()->delete();
            $cart->update(['total_amount' => 0]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    public function cancel($id)
    {
        $order = Order::where('user_id', Auth::id())
                     ->whereIn('status', ['pending', 'processing'])
                     ->with('items.product')
                     ->findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Restore stock when order is cancelled
            foreach ($order->items as $item) {
                $product = $item->product;
                $oldStock = $product->stock;
                $product->increment('stock', $item->quantity);
                
                // Log inventory change
                InventoryLog::create([
                    'product_id' => $product->id,
                    'quantity_change' => $item->quantity,
                    'old_quantity' => $oldStock,
                    'new_quantity' => $product->stock,
                    'reason' => 'order_cancelled',
                    'reference_id' => $order->id,
                    'notes' => "Order #{$order->order_number} cancelled - restored {$item->quantity} x {$item->product->name}"
                ]);
            }
            
            // Update order status
            $order->update(['status' => 'cancelled']);
            
            // Update payment status
            if ($order->payment) {
                $order->payment->update(['status' => 'refunded']);
            }
            
            // Update delivery status
            if ($order->delivery) {
                $order->delivery->update(['status' => 'cancelled']);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin: View all orders
     */
    public function adminIndex(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        
        $query = Order::with(['user', 'payment', 'delivery']);
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.orders.index', compact('orders'));
    }
    
    /**
     * Admin: Update order status
     */
    public function adminUpdateStatus(Request $request, Order $order)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);
        
        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);
        
        // Update delivery status
        if ($order->delivery) {
            $order->delivery->update([
                'status' => $request->status,
                'estimated_delivery' => $request->status === 'shipped' ? now()->addDays(3) : $order->delivery->estimated_delivery
            ]);
        }
        
        // If delivered, mark payment as completed for COD
        if ($request->status == 'delivered' && $order->payment && $order->payment->payment_method == 'cod') {
            $order->payment->update(['status' => 'completed']);
        }
        
        return redirect()->back()->with('success', "Order status updated from {$oldStatus} to {$request->status}");
    }
}