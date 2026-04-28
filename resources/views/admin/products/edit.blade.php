@extends('admin.layouts.admin')

@section('page-title', 'Edit Product')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Product: {{ $product->name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ $product->sku }}">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ $product->description }}</textarea>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Price *</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="{{ $product->price }}" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stock *</label>
                    <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-control" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $product->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$product->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                @if($product->image_url)
                <div class="col-md-12 mb-3">
                    <label>Current Image</label><br>
                    <img src="{{ $product->image_url }}" style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                @endif
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">New Image (Optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection