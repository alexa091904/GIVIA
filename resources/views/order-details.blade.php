@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Order #{{ $order->order_number }}</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <h3 class="text-sm text-gray-600">Order Status</h3>
                    <p class="text-lg font-semibold text-gray-900">{{ ucfirst($order->status) }}</p>
                </div>
                <div>
                    <h3 class="text-sm text-gray-600">Order Date</h3>
                    <p class="text-lg font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <h3 class="text-sm text-gray-600">Total Amount</h3>
                    <p class="text-lg font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div>
                    <h3 class="text-sm text-gray-600">Payment Method</h3>
                    <p class="text-lg font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Order Items</h2>
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Product</th>
                        <th class="text-center py-2">Quantity</th>
                        <th class="text-right py-2">Price</th>
                        <th class="text-right py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3">{{ $item->product->name }}</td>
                        <td class="text-center py-3">{{ $item->quantity }}</td>
                        <td class="text-right py-3">₱{{ number_format($item->price, 2) }}</td>
                        <td class="text-right py-3 font-semibold">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($order->shipping_address)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Shipping Address</h2>
            <p class="text-gray-700">{{ $order->shipping_address }}</p>
        </div>
        @endif

        @if($order->payment)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Payment Status</h2>
            <p class="text-sm text-gray-600">Status: <span class="font-semibold">{{ ucfirst($order->payment->status) }}</span></p>
            @if($order->payment->transaction_id)
            <p class="text-sm text-gray-600">Transaction ID: <span class="font-semibold">{{ $order->payment->transaction_id }}</span></p>
            @endif
        </div>
        @endif

        @if($order->delivery)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Delivery Status</h2>
            <p class="text-sm text-gray-600">Status: <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $order->delivery->status)) }}</span></p>
            @if($order->delivery->tracking_number)
            <p class="text-sm text-gray-600">Tracking: <span class="font-semibold">{{ $order->delivery->tracking_number }}</span></p>
            @endif
            @if($order->delivery->estimated_delivery_date)
            <p class="text-sm text-gray-600">Estimated Delivery: <span class="font-semibold">{{ $order->delivery->estimated_delivery_date }}</span></p>
            @endif
        </div>
        @endif

        <div class="flex gap-2">
            <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back to Orders</a>
            @if($order->status === 'pending')
            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Cancel this order?');">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Cancel Order</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
