@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<div class="row">
    <div class="col-md-3">
        <!-- Sidebar filters -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filter by Category</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ !request('category') ? 'active' : '' }}">
                        All Categories
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->id]) }}" 
                           class="list-group-item list-group-item-action {{ request('category') == $category->id ? 'active' : '' }}">
                            {{ $category->name }}
                            <span class="badge bg-secondary float-end">{{ $category->products_count ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Search Products</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search products..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filter by Price</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <div class="mb-2">
                        <label>Min Price</label>
                        <input type="number" name="min_price" class="form-control" 
                               placeholder="Min" value="{{ request('min_price') }}">
                    </div>
                    <div class="mb-2">
                        <label>Max Price</label>
                        <input type="number" name="max_price" class="form-control" 
                               placeholder="Max" value="{{ request('max_price') }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                    <a href="{{ route('products.index') }}" class="btn btn-link w-100 mt-2">Clear Filters</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products</h1>
            <div>
                <select class="form-select" id="sortSelect">
                    <option value="created_at_desc" {{ request('sort') == 'created_at' && request('order') == 'desc' ? 'selected' : '' }}>Newest</option>
                    <option value="price_asc" {{ request('sort') == 'price' && request('order') == 'asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price' && request('order') == 'desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name_asc" {{ request('sort') == 'name' && request('order') == 'asc' ? 'selected' : '' }}>Name: A to Z</option>
                </select>
            </div>
        </div>

        <div class="row">
            @forelse($products as $product)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $product->name }}">
                        @else
                            <div class="bg-light text-center py-5" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($product->description, 80) }}</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="h4 text-primary">${{ number_format($product->price, 2) }}</span>
                                @if($product->stock > 0)
                                    <button class="btn btn-sm btn-success add-to-cart" data-product-id="{{ $product->id }}">
                                        <i class="fas fa-cart-plus"></i> Add
                                    </button>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-link btn-sm p-0">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your search or filter criteria.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add to cart functionality
    $('.add-to-cart').click(function() {
        let productId = $(this).data('product-id');
        let button = $(this);
        let originalHtml = button.html();
        
        $.ajax({
            url: '/api/cart/add',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: 1,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            },
            success: function(response) {
                button.html('<i class="fas fa-check"></i> Added!');
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

    // Sort functionality
    $('#sortSelect').change(function() {
        let value = $(this).val();
        let parts = value.split('_');
        let sort = parts[0];
        let order = parts[1];
        
        let url = new URL(window.location.href);
        url.searchParams.set('sort', sort);
        url.searchParams.set('order', order);
        window.location.href = url.toString();
    });
});
</script>
@endpush