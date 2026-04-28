@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<h1 class="mb-4">Shopping Cart</h1>

@if(isset($cart) && $cart->items && $cart->items->count() > 0)
    <div class="row">
        <div class="col-md-8">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart->items as $item)
                            <tr data-item-id="{{ $item->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $item->product->image_url ?? 'https://via.placeholder.com/50x50' }}" 
                                             style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                        <div>
                                            <strong>{{ $item->product->name }}</strong>
                                            <small class="d-block text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${{ number_format($item->product->price, 2) }}</td>
                                <td style="width: 120px;">
                                    <input type="number" class="form-control form-control-sm update-quantity" 
                                           value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}"
                                           data-item-id="{{ $item->id }}">
                                </td>
                                <td class="item-subtotal">${{ number_format($item->quantity * $item->product->price, 2) }}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm remove-item" data-item-id="{{ $item->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong id="subtotal">${{ number_format($total, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <strong>Calculated at checkout</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total:</span>
                        <strong class="h5 text-primary" id="total">${{ number_format($total, 2) }}</strong>
                    </div>
                    <a href="{{ route('checkout') }}" class="btn btn-success w-100">
                        Proceed to Checkout
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-link w-100 mt-2">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any items to your cart yet.</p>
        <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function updateCartTotals() {
        let subtotal = 0;
        $('.item-subtotal').each(function() {
            subtotal += parseFloat($(this).text().replace('$', ''));
        });
        $('#subtotal').text('$' + subtotal.toFixed(2));
        $('#total').text('$' + subtotal.toFixed(2));
    }

    $('.update-quantity').change(function() {
        let itemId = $(this).data('item-id');
        let quantity = $(this).val();
        let row = $(this).closest('tr');
        
        $.ajax({
            url: '/api/cart/update/' + itemId,
            method: 'PUT',
            data: {
                quantity: quantity,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                let price = parseFloat(row.find('td:eq(1)').text().replace('$', ''));
                let newSubtotal = price * quantity;
                row.find('.item-subtotal').text('$' + newSubtotal.toFixed(2));
                updateCartTotals();
                updateCartCount();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.error || 'Error updating quantity');
                location.reload();
            }
        });
    });

    $('.remove-item').click(function() {
        let itemId = $(this).data('item-id');
        
        if (confirm('Remove this item from cart?')) {
            $.ajax({
                url: '/api/cart/remove/' + itemId,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error removing item');
                }
            });
        }
    });
</script>
@endpush