@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-4xl font-bold mb-8">Shop by Category</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
            <h2 class="text-2xl font-bold mb-2">{{ $category->name }}</h2>
            <p class="text-gray-600 mb-4">{{ $category->description ?? 'No description' }}</p>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">{{ $category->products_count }} product{{ $category->products_count != 1 ? 's' : '' }}</span>
                <a href="{{ route('categories.show', $category->id) }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    View Products
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-8">
            <p class="text-gray-500">No categories available.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
