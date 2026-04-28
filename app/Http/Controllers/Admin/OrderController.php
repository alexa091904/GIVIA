<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'payment', 'delivery'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }
    
    public function show(Order $order)
    {
        $order->load(['items.product', 'user', 'payment', 'delivery']);
        return view('admin.orders.show', compact('order'));
    }
    
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);
        
        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);
        
        if ($order->delivery) {
            $order->delivery->update([
                'status' => $request->status,
                'estimated_delivery' => $request->status === 'shipped' ? now()->addDays(3) : $order->delivery->estimated_delivery
            ]);
        } else {
            Delivery::create([
                'order_id' => $order->id,
                'status' => $request->status,
                'estimated_delivery' => now()->addDays(5)
            ]);
        }
        
        // If delivered, mark payment as completed for COD
        if ($request->status == 'delivered' && $order->payment && $order->payment->payment_method == 'cod') {
            $order->payment->update(['status' => 'completed']);
        }
        
        return redirect()->back()->with('success', "Order status updated from {$oldStatus} to {$request->status}");
    }
}