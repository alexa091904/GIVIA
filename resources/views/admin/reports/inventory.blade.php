@extends('layouts.admin')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-4xl font-bold mb-8">Inventory Report</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold mb-2">Low Stock Products</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $lowStock->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold mb-2">Out of Stock</h3>
            <p class="text-3xl font-bold text-red-600">{{ $outOfStock->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold mb-2">Total Products</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $lowStock->count() + $outOfStock->count() }}</p>
        </div>
    </div>

    @if($lowStock->count() > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Low Stock Products (≤ 10 units)</h2>
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Product Name</th>
                    <th class="text-center py-2">Current Stock</th>
                    <th class="text-center py-2">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStock as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3">{{ $product->name }}</td>
                    <td class="text-center py-3"><span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded">{{ $product->stock }}</span></td>
                    <td class="text-center py-3">₱{{ number_format($product->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($outOfStock->count() > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Out of Stock Products</h2>
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Product Name</th>
                    <th class="text-center py-2">Status</th>
                    <th class="text-center py-2">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($outOfStock as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3">{{ $product->name }}</td>
                    <td class="text-center py-3"><span class="px-3 py-1 bg-red-100 text-red-800 rounded">Out of Stock</span></td>
                    <td class="text-center py-3">₱{{ number_format($product->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Top Selling Products</h2>
        @if($topSelling->count() > 0)
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Product Name</th>
                    <th class="text-center py-2">Units Sold</th>
                    <th class="text-center py-2">Current Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topSelling as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3">{{ $product->name }}</td>
                    <td class="text-center py-3">{{ $product->sold }}</td>
                    <td class="text-center py-3">{{ $product->stock }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-500">No sales data available.</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Recent Inventory Changes</h2>
        @if($inventoryLogs->count() > 0)
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Product</th>
                    <th class="text-center py-2">Reason</th>
                    <th class="text-center py-2">Change</th>
                    <th class="text-center py-2">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventoryLogs as $log)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3">{{ $log->product->name }}</td>
                    <td class="text-center py-3"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">{{ ucfirst($log->reason) }}</span></td>
                    <td class="text-center py-3"><span class="font-semibold {{ $log->quantity_change > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }}</span></td>
                    <td class="text-center py-3">{{ $log->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-500">No inventory logs available.</p>
        @endif
    </div>
</div>
@endsection
