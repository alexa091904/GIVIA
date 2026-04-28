@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="row">
    <div class="col-md-6">
        @if($product->image_url)
            <img src="{{ $product->image_url }}" class="img-fluid rounded" alt="{{ $product->name }}">
        @else
            <div class="bg-light text-center py-5 rounded">
                <i class="fas fa-image fa-5x text-muted"></i>
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                @if($product->category)
                    <li class="breadcrumb-item">{{ $product->category->name }}</li>
                @endif
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>

        <h1>{{ $product->name }}</h1>
        
        @if($product->sku)
            <p class="text-muted">SKU: {{ $product->sku }}</p>
        @endif
        
        <h2 class="text-primary mb-3">${{ number_format($product->price, 2) }}</h2>
        
        <div class="mb-4">
            <strong>Availability:</strong>
            @if($product->stock > 0)
                <span class="text-success">In Stock ({{ $product->stock }} units available)</span>
            @else
                <span class="text-danger">Out of Stock</span>
            @endif
        </div>

        <div class="mb-4">
            <strong>Description:</strong>
            <p class="mt-2">{{ $product->description }}</p>
        </div>

        @if($product->stock > 0)
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" id="quantity" class="form-control" value="1" min="1" max="{{ $product->stock }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary btn-lg w-100" id="addToCartBtn">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
        @endif

        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="mt-5">
                <h4>Related Products</h4>
                <div class="row">
                    @foreach($relatedProducts as $related)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                @if($related->image_url)
                                    <img src="{{ $related->image_url }}" class="card-img-top" style="height: 100px; object-fit: cover;">
                                @endif
                                <div class="card-body">
                                    <h6 class="card-title">{{ $related->name }}</h6>
                                    <p class="text-primary">${{ number_format($related->price, 2) }}</p>
                                    <a href="{{ route('products.show', $related->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#addToCartBtn').click(function() {
        let quantity = $('#quantity').val();
        let button = $(this);
        let originalHtml = button.html();
        
        $.ajax({
            url: '/api/cart/add',
            method: 'POST',
            data: {
                product_id: {{ $product->id }},
                quantity: quantity,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                button.html('<i class="fas fa-spinner fa-spin"></i> Adding...').prop('disabled', true);
            },
            success: function(response) {
                button.html('<i class="fas fa-check"></i> Added to Cart!');
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                setTimeout(() => {
                    button.html(originalHtml).prop('disabled', false);
                }, 2000);
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.error || 'Error adding to cart';
                alert(errorMsg);
                button.html(originalHtml).prop('disabled', false);
            }
        });
    });
</script>
@endpush