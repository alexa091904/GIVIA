@extends('admin.layouts.admin')

@section('page-title', 'Inventory Management')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Stock Levels</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="inventoryTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Adjust Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku ?? 'N/A' }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>
                        @if($product->stock <= 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($product->stock <= 5)
                            <span class="badge bg-warning">Low Stock</span>
                        @else
                            <span class="badge bg-success">In Stock</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.inventory.adjust', $product->id) }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <input type="number" name="quantity" class="form-control form-control-sm" style="width: 100px;" placeholder="+/- amount">
                            <button type="submit" class="btn btn-sm btn-primary">Adjust</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#inventoryTable').DataTable();
    });
</script>
@endpush