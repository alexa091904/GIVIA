@extends('admin.layouts.admin')

@section('page-title', 'User Details: ' . $user->name)

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 20px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .order-items-toggle {
        cursor: pointer;
    }
    .order-items-toggle:hover {
        opacity: 0.8;
    }
    .order-items-row {
        background-color: #f8f9fa;
    }
    .stat-box {
        transition: transform 0.2s;
    }
    .stat-box:hover {
        transform: translateY(-3px);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- User Information Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> User Information
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                    <h4 class="mt-2">{{ $user->name }}</h4>
                </div>
                
                <table class="table table-borderless">
                    <tr>
                        <th width="35%">User ID:</th>
                        <td>#{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td><strong>{{ $user->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td>
                            <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <select name="role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <option value="user" {{ ($user->role ?? 'user') == 'user' ? 'selected' : '' }}>User</option>
                                    <option value="admin" {{ ($user->role ?? 'user') == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                @if($user->id === auth()->id())
                                    <small class="text-muted d-block">(You cannot change your own role)</small>
                                @endif
                            </form>
                         </td>
                    </tr>
                    <tr>
                        <th>Joined:</th>
                        <td>{{ $user->created_at->format('F d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Last Update:</th>
                        <td>{{ $user->updated_at->diffForHumans() }}</td>
                    </tr>
                </table>
                
                <div class="mt-3">
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    @else
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-lock"></i> Cannot delete yourself
                        </button>
                    @endif
                    
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm float-end">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Card -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> User Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-2 stat-box">
                            <div class="h2 text-primary">{{ $user->orders->count() }}</div>
                            <small>Total Orders</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-2 stat-box">
                            <div class="h2 text-success">${{ number_format($user->orders->sum('total_amount'), 2) }}</div>
                            <small>Total Spent</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 stat-box">
                            <div class="h2 text-warning">{{ $user->orders->where('status', 'pending')->count() }}</div>
                            <small>Pending Orders</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 stat-box">
                            <div class="h2 text-success">{{ $user->orders->where('status', 'delivered')->count() }}</div>
                            <small>Delivered</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cart Information Card -->
        @if($user->cart && $user->cart->items->count() > 0)
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart"></i> Current Cart
                </h5>
            </div>
            <div class="card-body">
                <p><strong>{{ $user->cart->items->count() }}</strong> items in cart</p>
                <p>Cart Total: <strong>${{ number_format($user->cart->total_amount, 2) }}</strong></p>
                <button class="btn btn-sm btn-danger" onclick="confirmClearCart({{ $user->id }})">
                    <i class="fas fa-trash"></i> Clear Cart
                </button>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-8">
        <!-- Orders History Card -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-bag"></i> Order History
                </h5>
            </div>
            <div class="card-body">
                @if($user->orders && $user->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                     </td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items->count() }} items</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                'refunded' => 'secondary'
                                            ];
                                            $color = $statusColors[$order->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
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
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button class="btn btn-sm btn-secondary order-items-toggle" data-order-id="{{ $order->id }}">
                                            <i class="fas fa-list"></i> Items
                                        </button>
                                        @if(in_array($order->status, ['pending', 'processing']))
                                            <button class="btn btn-sm btn-danger cancel-order mt-1" data-order-id="{{ $order->id }}">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        @endif
                                     </td>
                                </tr>
                                
                                <!-- Order Items Sub-row -->
                                <tr class="order-items-{{ $order->id }}" style="display: none;">
                                    <td colspan="7" class="bg-light">
                                        <div class="p-3">
                                            <strong>Order Items:</strong>
                                            <table class="table table-sm mt-2">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Price</th>
                                                        <th>Quantity</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($order->items as $item)
                                                    <tr>
                                                        <td>{{ $item->product->name }}</td>
                                                        <td>${{ number_format($item->price, 2) }}</td>
                                                        <td>{{ $item->quantity }}</td>
                                                        <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                             </table>
                                        </div>
                                     </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('admin.orders.index') }}?user_id={{ $user->id }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View All Orders
                        </a>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h5>No orders yet</h5>
                        <p class="text-muted">This user hasn't placed any orders.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Activity Card -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> Recent Activity
                </h5>
            </div>
            <div class="card-body">
                @php
                    $activities = [];
                    
                    // Define status colors for activities
                    $statusColors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'secondary'
                    ];
                    
                    // Add order activities
                    foreach($user->orders->sortByDesc('created_at')->take(10) as $order) {
                        $activities[] = [
                            'type' => 'order',
                            'message' => "Placed order #{$order->order_number} for $" . number_format($order->total_amount, 2),
                            'date' => $order->created_at,
                            'status' => $order->status
                        ];
                    }
                    
                    // Sort by date
                    $activities = collect($activities)->sortByDesc('date')->take(10);
                @endphp
                
                @if($activities->count() > 0)
                    <div class="timeline">
                        @foreach($activities as $activity)
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <i class="fas fa-circle text-primary" style="font-size: 12px;"></i>
                                </div>
                                <div>
                                    <strong>{{ $activity['message'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                    @if(isset($activity['status']))
                                        <span class="badge bg-{{ $statusColors[$activity['status']] ?? 'secondary' }} ms-2">
                                            {{ ucfirst($activity['status']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">No recent activity</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal for clearing cart -->
<div class="modal fade" id="clearCartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear User Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear this user's cart?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="clearCartForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Clear Cart</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmClearCart(userId) {
    const modal = new bootstrap.Modal(document.getElementById('clearCartModal'));
    const form = document.getElementById('clearCartForm');
    form.action = '{{ url("/admin/users") }}/' + userId + '/clear-cart';
    modal.show();
}

// Toggle order items visibility
$(document).ready(function() {
    $('.order-items-toggle').click(function() {
        let orderId = $(this).data('order-id');
        $('.order-items-' + orderId).toggle();
        // Change button text/icon
        let icon = $(this).find('i');
        if ($('.order-items-' + orderId).is(':visible')) {
            icon.removeClass('fa-list').addClass('fa-minus');
        } else {
            icon.removeClass('fa-minus').addClass('fa-list');
        }
    });
    
    // Cancel order
    $('.cancel-order').click(function() {
        let orderId = $(this).data('order-id');
        if (confirm('Cancel this order? Items will be restocked.')) {
            $.ajax({
                url: '/api/orders/' + orderId + '/cancel',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.error || 'Error cancelling order');
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.error || 'Error cancelling order');
                }
            });
        }
    });
});
</script>
@endpush