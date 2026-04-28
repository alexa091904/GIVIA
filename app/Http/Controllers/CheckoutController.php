<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page
     */
    public function index()
    {
        // Get user's cart with items
        $cart = Cart::where('user_id', Auth::id())
                    ->with('items.product')
                    ->first();
        
        // Check if cart exists and has items
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                           ->with('error', 'Your cart is empty. Please add some items first.');
        }
        
        // Calculate totals
        $subtotal = $cart->items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        // Get coupon discount if applied
        $discount = session('discount', 0);
        $coupon = session('coupon');
        
        // Calculate shipping (simple logic - can be enhanced)
        $shipping = $subtotal > 50 ? 0 : 5.99;
        
        // Calculate tax (10% tax rate)
        $tax = ($subtotal - $discount) * 0.10;
        
        // Calculate total
        $total = $subtotal - $discount + $shipping + $tax;
        
        return view('checkout', compact(
            'cart', 
            'subtotal', 
            'discount', 
            'coupon', 
            'shipping', 
            'tax', 
            'total'
        ));
    }
    
    /**
     * Process the checkout (if you want to handle form submission here)
     * Note: Currently orders are created via API, but you can add this method if needed
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'payment_method' => 'required|in:cod,credit_card,paypal'
        ]);
        
        // This will redirect to API order creation
        // Or you can implement order creation logic here
        
        return redirect()->route('orders.index')
                        ->with('success', 'Order placed successfully!');
    }
}