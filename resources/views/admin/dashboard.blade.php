@extends('admin.layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-box fa-2x text-primary"></i>
            <div class="stat-number">{{ $totalProducts ?? 0 }}</div>
            <div>Total Products</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-shopping-cart fa-2x text-success"></i>
            <div class="stat-number">{{ $totalOrders ?? 0 }}</div>
            <div>Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-users fa-2x text-info"></i>
            <div class="stat-number">{{ $totalUsers ?? 0 }}</div>
            <div>Total Users</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-dollar-sign fa-2x text-warning"></i>
            <div class="stat-number">${{ number_format($totalRevenue ?? 0, 2) }}</div>
            <div>Total Revenue</div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Recent Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders ?? [] as $order)
                            <tr>
                                <td>{{ $order->order_number ?? 'N/A' }}</td>
                                <td>{{ $order->user->name ?? 'N/A' }}</td>
                                <td>${{ number_format($order->total_amount ?? 0, 2) }}</td>
                                <td>
                                    <span class="status-badge status-{{ $order->status ?? 'pending' }}">
                                        {{ ucfirst($order->status ?? 'Pending') }}
                                    </span>
                                </td>
                                <td>{{ isset($order->created_at) ? $order->created_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No orders found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Low Stock Alert</h5>
            </div>
            <div class="card-body">
                @forelse($lowStockProducts ?? [] as $product)
                    <div class="alert alert-warning">
                        <strong>{{ $product->name ?? 'Unknown' }}</strong><br>
                        Stock: {{ $product->stock ?? 0 }} units
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning mt-2">Restock</a>
                    </div>
                @empty
                    <p class="text-success">All products have sufficient stock!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection