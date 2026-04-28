<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::withCount('orders')
                     ->orderBy('created_at', 'desc')
                     ->paginate(20);
        return view('admin.users.index', compact('users'));
    }
    
    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Load relationships
        $user->load(['orders.items.product', 'cart.items.product']);
        return view('admin.users.show', compact('user'));
    }
    
    /**
     * Update user role
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin'
        ]);
        
        // Prevent changing own role
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }
        
        $oldRole = $user->role ?? 'user';
        $user->update(['role' => $request->role]);
        
        return back()->with('success', "User role changed from {$oldRole} to {$request->role}");
    }
    
    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        
        // Check if user has orders
        if ($user->orders()->count() > 0) {
            return back()->with('error', 'Cannot delete user with existing orders.');
        }
        
        // Delete user's cart first
        if ($user->cart) {
            $user->cart->items()->delete();
            $user->cart->delete();
        }
        
        $userName = $user->name;
        $user->delete();
        
        return redirect()->route('admin.users.index')
                        ->with('success', "User '{$userName}' deleted successfully.");
    }
    
    /**
     * Clear user's cart
     */
    public function clearCart(User $user)
    {
        if ($user->cart) {
            $itemCount = $user->cart->items->count();
            $user->cart->items()->delete();
            $user->cart->update(['total_amount' => 0]);
            
            return back()->with('success', "Cleared {$itemCount} items from user's cart.");
        }
        
        return back()->with('info', 'User cart is already empty.');
    }
}