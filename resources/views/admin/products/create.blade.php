@extends('admin.layouts.admin')

@section('page-title', 'Add New Product')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Product Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control">
                    <small class="text-muted">Unique product code (optional)</small>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Price *</label>
                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" required>
                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stock *</label>
                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" required>
                    @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection