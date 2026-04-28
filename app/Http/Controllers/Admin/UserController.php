<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('orders')
                     ->orderBy('created_at', 'desc')
                     ->paginate(20);
        
        // FIXED: Changed from 'admin.user.index' to 'admin.users.index'
        return view('admin.users.index', compact('users'));
    }
    
    public function show(User $user)
    {
        $user->load('orders.items.product');
        
        // FIXED: Changed from 'admin.user.show' to 'admin.users.show'
        return view('admin.users.show', compact('user'));
    }
    
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin'
        ]);
        
        // Prevent changing own role
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }
        
        $user->update(['role' => $request->role]);
        
        return back()->with('success', "User role updated to {$request->role}");
    }
    
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        
        // Check if user has orders before deleting
        if ($user->orders()->count() > 0) {
            return back()->with('error', 'Cannot delete user with existing orders.');
        }
        
        // Delete user's cart first
        if ($user->cart) {
            $user->cart->items()->delete();
            $user->cart->delete();
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
                        ->with('success', 'User deleted successfully.');
    }
    
    // Add this method if you want to clear user's cart
    public function clearCart(User $user)
    {
        if ($user->cart) {
            $user->cart->items()->delete();
            $user->cart->update(['total_amount' => 0]);
        }
        
        return back()->with('success', 'User cart cleared successfully.');
    }
}