<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function track(Order $order)
    {
        // Check authorization
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $delivery = $order->delivery;
        
        if (!$delivery) {
            return response()->json(['error' => 'No delivery information found'], 404);
        }
        
        return response()->json([
            'status' => $delivery->status,
            'tracking_number' => $delivery->tracking_number,
            'estimated_delivery' => $delivery->estimated_delivery ?? $delivery->estimated_delivery_date,  // Handle both column names
            'current_location' => $delivery->current_location,
            'updates' => $delivery->updates_history
        ]);
    }
    
    // Admin only methods below
    public function updateStatus(Request $request, Order $order)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string',
            'current_location' => 'nullable|string'
        ]);
        
        $delivery = $order->delivery;
        
        if (!$delivery) {
            // Determine which column name to use
            $estimatedColumn = 'estimated_delivery';
            if (!schema()->hasColumn('deliveries', 'estimated_delivery') && schema()->hasColumn('deliveries', 'estimated_delivery_date')) {
                $estimatedColumn = 'estimated_delivery_date';
            }
            
            $delivery = Delivery::create([
                'order_id' => $order->id,
                'status' => $request->status,
                'tracking_number' => $request->tracking_number,
                'current_location' => $request->current_location,
                $estimatedColumn => now()->addDays(5)
            ]);
        } else {
            // Track status history
            $history = $delivery->updates_history ?? [];
            $history[] = [
                'status' => $request->status,
                'location' => $request->current_location,
                'timestamp' => now()->toDateTimeString()
            ];
            
            $delivery->update([
                'status' => $request->status,
                'tracking_number' => $request->tracking_number ?? $delivery->tracking_number,
                'current_location' => $request->current_location,
                'updates_history' => $history
            ]);
            
            // If delivered, update order status
            if ($request->status === 'delivered') {
                $order->update(['status' => 'delivered']);
                
                // Update payment status for COD orders
                if ($order->payment && $order->payment->payment_method === 'cod') {
                    $order->payment->update(['status' => 'completed']);
                }
            }
        }
        
        return response()->json(['message' => 'Delivery status updated', 'delivery' => $delivery]);
    }
}