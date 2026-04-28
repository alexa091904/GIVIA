<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // Process payment for an order
    public function process(Request $request, Order $order)
    {
        // Verify order belongs to user
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Check if already paid
        if ($order->payment && $order->payment->status === 'completed') {
            return response()->json(['error' => 'Order already paid'], 400);
        }
        
        $request->validate([
            'payment_method' => 'required|in:credit_card,paypal,cod',
            'card_number' => 'required_if:payment_method,credit_card|nullable|string',
            'expiry_date' => 'required_if:payment_method,credit_card|nullable|string',
            'cvv' => 'required_if:payment_method,credit_card|nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            // In real implementation, you'd call Stripe/PayPal API here
            // For now, simulate payment processing
            $paymentStatus = $this->simulatePaymentProcessing($request);
            
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'amount' => $order->total_amount,
                    'payment_method' => $request->payment_method,
                    'status' => $paymentStatus,
                    'transaction_id' => 'TXN_' . uniqid()
                ]
            );
            
            if ($paymentStatus === 'completed') {
                $order->update(['status' => 'processing']);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Payment ' . $paymentStatus,
                'payment' => $payment
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }
    
    // Check payment status
    public function status(Order $order)
    {
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $payment = $order->payment;
        
        if (!$payment) {
            return response()->json(['status' => 'pending', 'message' => 'No payment initiated']);
        }
        
        return response()->json([
            'status' => $payment->status,
            'amount' => $payment->amount,
            'method' => $payment->payment_method,
            'transaction_id' => $payment->transaction_id
        ]);
    }
    
    // Refund payment
    public function refund(Order $order)
    {
        // Only admin can refund
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $payment = $order->payment;
        
        if (!$payment || $payment->status !== 'completed') {
            return response()->json(['error' => 'Cannot refund - payment not completed'], 400);
        }
        
        DB::beginTransaction();
        
        try {
            // In real implementation, call payment gateway refund API
            $payment->update(['status' => 'refunded']);
            $order->update(['status' => 'refunded']);
            
            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
            
            DB::commit();
            
            return response()->json(['message' => 'Refund processed successfully']);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Refund failed'], 500);
        }
    }
    
    private function simulatePaymentProcessing($request)
    {
        // For COD, always "pending" until delivered
        if ($request->payment_method === 'cod') {
            return 'pending';
        }
        
        // Simulate success/failure (95% success rate for demo)
        return rand(1, 100) <= 95 ? 'completed' : 'failed';
    }
}