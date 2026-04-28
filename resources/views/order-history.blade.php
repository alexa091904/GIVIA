@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<h1 class="mb-4">My Orders</h1>

@if($orders && $orders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="order-status status-{{ $order->status }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>
                            @if($order->payment)
                                <span class="badge bg-{{ $order->payment->status === 'completed' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($order->payment->status) }}
                                </span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($order->delivery)
                                <span class="badge bg-info">{{ ucfirst($order->delivery->status) }}</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                View Details
                            </a>
                            @if(in_array($order->status, ['pending', 'processing']))
                                <button class="btn btn-sm btn-danger cancel-order mt-1" data-order-id="{{ $order->id }}">
                                    Cancel
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
        <h3>No orders yet</h3>
        <p>You haven't placed any orders yet.</p>
        <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
    </div>
@endif
@endsection

@push('scripts')
<script>
    $('.cancel-order').click(function() {
        let orderId = $(this).data('order-id');
        
        if (confirm('Are you sure you want to cancel this order?')) {
            $.ajax({
                url: '/api/orders/' + orderId + '/cancel',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.error || 'Error cancelling order');
                }
            });
        }
    });
</script>
@endpush