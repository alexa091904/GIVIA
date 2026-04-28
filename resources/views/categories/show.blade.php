@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('categories.index') }}" class="text-blue-500 hover:text-blue-700">&larr; Back to Categories</a>
    </div>

    <h1 class="text-4xl font-bold mb-2">{{ $category->name }}</h1>
    @if($category->description)
    <p class="text-gray-600 mb-8">{{ $category->description }}</p>
    @endif
    
    @if($category->products->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($category->products as $product)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
            @if($product->image)
            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
            @else
            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                <span class="text-gray-400">No Image</span>
            </div>
            @endif
            
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-2">{{ $product->name }}</h3>
                <p class="text-gray-600 text-sm mb-2">{{ Str::limit($product->description, 100) }}</p>
                <p class="text-2xl font-bold text-blue-600 mb-4">₱{{ number_format($product->price, 2) }}</p>
                
                @if($product->stock > 0)
                <span class="text-green-600 text-sm">In Stock ({{ $product->stock }})</span>
                @else
                <span class="text-red-600 text-sm">Out of Stock</span>
                @endif
                
                <div class="mt-4">
                    <a href="{{ route('products.show', $product->id) }}" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-center block">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8">
        <p class="text-gray-500 text-lg">No products in this category.</p>
    </div>
    @endif
</div>
@endsection
