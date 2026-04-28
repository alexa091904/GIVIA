@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<h1 class="mb-4">Checkout</h1>

<div class="row">
    <div class="col-md-7">
        <form id="checkoutForm">
            @csrf
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shipping Address</label>
                        <textarea name="shipping_address" class="form-control" rows="3" required placeholder="Enter your complete shipping address"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Billing Address</label>
                        <textarea name="billing_address" class="form-control" rows="3" required placeholder="Enter your complete billing address"></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="cod" id="cod" checked>
                        <label class="form-check-label" for="cod">
                            <i class="fas fa-money-bill"></i> Cash on Delivery (COD)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="credit_card" id="credit_card">
                        <label class="form-check-label" for="credit_card">
                            <i class="fas fa-credit-card"></i> Credit Card
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="paypal" id="paypal">
                        <label class="form-check-label" for="paypal">
                            <i class="fab fa-paypal"></i> PayPal
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100">Place Order</button>
        </form>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body" id="cartItems">
                <div class="text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function loadCart() {
        $.get('/api/cart', function(data) {
            let html = '';
            let total = 0;
            
            if (data.cart && data.cart.items && data.cart.items.length > 0) {
                data.cart.items.forEach(item => {
                    let subtotal = item.quantity * item.product.price;
                    total += subtotal;
                    html += `
                        <div class="d-flex justify-content-between mb-2">
                            <span>${item.product.name} x ${item.quantity}</span>
                            <span>$${subtotal.toFixed(2)}</span>
                        </div>
                    `;
                });
                html += '<hr>';
                html += `<div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>$${total.toFixed(2)}</span>
                         </div>`;
            } else {
                html = '<p class="text-muted">Your cart is empty</p>';
            }
            
            $('#cartItems').html(html);
        }).fail(function() {
            $('#cartItems').html('<p class="text-danger">Error loading cart. Please refresh.</p>');
        });
    }
    
    loadCart();
    
    $('#checkoutForm').submit(function(e) {
        e.preventDefault();
        
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
        
        $.ajax({
            url: '/api/orders',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert('Order placed successfully! Order #: ' + response.order_number);
                window.location.href = '/orders';
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Error placing order';
                alert(errorMsg);
                submitBtn.html('Place Order').prop('disabled', false);
            }
        });
    });
</script>
@endpush