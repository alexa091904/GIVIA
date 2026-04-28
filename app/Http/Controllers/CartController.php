<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Display cart contents
     */
    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cart->load('items.product.category');
        
        $subtotal = $cart->items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        // Apply coupon discount if exists
        $discount = session('discount', 0);
        $total = $subtotal - $discount;
        
        // Get coupon info if applied
        $coupon = session('coupon');
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart' => $cart,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'coupon' => $coupon,
                'items_count' => $cart->items->count()
            ]);
        }
        
        return view('cart', compact('cart', 'subtotal', 'discount', 'total', 'coupon'));
    }
    
    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:999'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        
        // Check stock availability
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'error' => "Insufficient stock. Only {$product->stock} units available."
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $cart = $this->getOrCreateCart();
            
            // Check if product already in cart
            $cartItem = CartItem::where('cart_id', $cart->id)
                                ->where('product_id', $product->id)
                                ->first();
            
            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                
                // Check stock again for the new total
                if ($product->stock < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'error' => "Cannot add {$quantity} more. Only {$product->stock} units available."
                    ], 400);
                }
                
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity
                ]);
            }
            
            // Update cart total
            $this->updateCartTotal($cart);
            
            DB::commit();
            
            // Clear any applied coupon (cart changed)
            session()->forget(['coupon', 'discount']);
            
            return response()->json([
                'success' => true,
                'message' => "{$product->name} added to cart successfully!",
                'cart_total' => $cart->total_amount,
                'cart_count' => $cart->items->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Failed to add item to cart: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update cart item quantity
     */
    public function update(Request $request, $itemId)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:999'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $cartItem = CartItem::with('product')->findOrFail($itemId);
            $cart = $cartItem->cart;
            
            // Verify cart ownership
            if (!$this->verifyCartOwnership($cart)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to cart'
                ], 403);
            }
            
            // Check stock availability
            if ($cartItem->product->stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'error' => "Insufficient stock. Only {$cartItem->product->stock} units available."
                ], 400);
            }
            
            // Update quantity
            $cartItem->update(['quantity' => $request->quantity]);
            
            // Update cart total
            $this->updateCartTotal($cart);
            
            DB::commit();
            
            // Clear applied coupon (cart changed)
            session()->forget(['coupon', 'discount']);
            
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_total' => $cart->total_amount,
                'item_subtotal' => $cartItem->quantity * $cartItem->product->price,
                'cart_count' => $cart->items->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Failed to update cart: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove item from cart
     */
    public function remove($itemId)
    {
        DB::beginTransaction();
        
        try {
            $cartItem = CartItem::findOrFail($itemId);
            $cart = $cartItem->cart;
            
            // Verify cart ownership
            if (!$this->verifyCartOwnership($cart)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to cart'
                ], 403);
            }
            
            $productName = $cartItem->product->name;
            $cartItem->delete();
            
            // Update cart total
            $this->updateCartTotal($cart);
            
            DB::commit();
            
            // Clear applied coupon (cart changed)
            session()->forget(['coupon', 'discount']);
            
            return response()->json([
                'success' => true,
                'message' => "{$productName} removed from cart",
                'cart_total' => $cart->total_amount,
                'cart_count' => $cart->items->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove item: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear entire cart
     */
    public function clear()
    {
        DB::beginTransaction();
        
        try {
            $cart = $this->getOrCreateCart();
            
            // Verify cart ownership
            if (!$this->verifyCartOwnership($cart)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to cart'
                ], 403);
            }
            
            $itemCount = $cart->items->count();
            $cart->items()->delete();
            $cart->update(['total_amount' => 0]);
            
            DB::commit();
            
            // Clear applied coupon
            session()->forget(['coupon', 'discount']);
            
            return response()->json([
                'success' => true,
                'message' => "Cart cleared successfully ({$itemCount} items removed)",
                'cart_total' => 0,
                'cart_count' => 0
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cart: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|exists:coupons,code'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid coupon code'
            ], 422);
        }
        
        $coupon = Coupon::where('code', $request->coupon_code)->first();
        
        // Check if coupon is valid
        if (!$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'error' => 'Coupon is invalid or expired'
            ], 400);
        }
        
        $cart = $this->getOrCreateCart();
        $subtotal = $cart->items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        // Calculate discount
        $discount = $coupon->applyDiscount($subtotal);
        
        if ($discount == 0 && $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'error' => "Minimum order amount of $" . number_format($coupon->min_order_amount, 2) . " required"
            ], 400);
        }
        
        // Store coupon in session
        session([
            'coupon' => $coupon,
            'discount' => $discount
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discount' => $discount,
            'subtotal' => $subtotal,
            'new_total' => $subtotal - $discount,
            'coupon' => $coupon
        ]);
    }
    
    /**
     * Remove coupon from cart
     */
    public function removeCoupon()
    {
        session()->forget(['coupon', 'discount']);
        
        $cart = $this->getOrCreateCart();
        $total = $cart->items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon removed',
            'total' => $total
        ]);
    }
    
    /**
     * Get cart summary (for AJAX badge updates)
     */
    public function summary()
    {
        $cart = $this->getOrCreateCart();
        $cart->load('items.product');
        
        $itemCount = $cart->items->sum('quantity');
        $subtotal = $cart->items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        $discount = session('discount', 0);
        $total = $subtotal - $discount;
        
        return response()->json([
            'success' => true,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total
        ]);
    }
    
    /**
     * Get or create cart with clear logic:
     * - If user is logged in: use user_id
     * - If guest: use session_id
     * - When guest logs in: merge guest cart with user cart
     */
    private function getOrCreateCart()
    {
        $userId = Auth::id();
        $sessionId = session()->getId();
        
        // If user is logged in
        if ($userId) {
            // Check for existing user cart
            $cart = Cart::where('user_id', $userId)->first();
            
            // Check for guest cart with same session
            $guestCart = Cart::where('session_id', $sessionId)
                             ->whereNull('user_id')
                             ->first();
            
            // If guest cart exists and no user cart, convert guest cart to user cart
            if ($guestCart && !$cart) {
                $guestCart->update([
                    'user_id' => $userId,
                    'session_id' => null
                ]);
                $cart = $guestCart;
            } 
            // If both guest cart and user cart exist, merge them
            elseif ($guestCart && $cart) {
                foreach ($guestCart->items as $guestItem) {
                    $existingItem = $cart->items->where('product_id', $guestItem->product_id)->first();
                    if ($existingItem) {
                        $existingItem->update([
                            'quantity' => $existingItem->quantity + $guestItem->quantity
                        ]);
                    } else {
                        $guestItem->update(['cart_id' => $cart->id]);
                    }
                }
                $guestCart->delete();
                $this->updateCartTotal($cart);
            }
            
            // If still no cart, create new user cart
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $userId,
                    'session_id' => null,
                    'total_amount' => 0
                ]);
            }
            
            return $cart;
        }
        
        // Guest user - use session_id
        $cart = Cart::where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->first();
        
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => null,
                'session_id' => $sessionId,
                'total_amount' => 0
            ]);
        }
        
        return $cart;
    }
    
    /**
     * Update cart total amount
     */
    private function updateCartTotal($cart)
    {
        $cart->load('items.product');
        
        $total = $cart->items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        $cart->update(['total_amount' => $total]);
        
        return $cart;
    }
    
    /**
     * Verify cart ownership
     */
    private function verifyCartOwnership($cart)
    {
        if (Auth::check()) {
            // Logged in user can only access their own cart
            return $cart->user_id === Auth::id();
        }
        
        // Guest can only access cart with matching session
        return $cart->session_id === session()->getId() && is_null($cart->user_id);
    }
}