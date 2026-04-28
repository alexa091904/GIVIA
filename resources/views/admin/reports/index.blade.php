@extends('admin.layouts.admin')

@section('page-title', 'Sales Reports')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Filter Reports</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number">{{ $summary['total_orders'] }}</div>
            <div>Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number">${{ number_format($summary['total_revenue'], 2) }}</div>
            <div>Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number">${{ number_format($summary['average_order_value'], 2) }}</div>
            <div>Average Order</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number">{{ $summary['pending_orders'] }}</div>
            <div>Pending Orders</div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Top Selling Products</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>Product</th><th>Units Sold</th></tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $product)
                        <tr><td>{{ $product->name }}</td><td>{{ $product->total_sold }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Recent Daily Sales</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>Date</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @foreach($dailySales as $sale)
                        <tr>
                            <td>{{ $sale->date }}</td>
                            <td>${{ number_format($sale->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Export Report</div>
            <div class="card-body">
                <a href="{{ route('admin.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                   class="btn btn-success">Download CSV Report</a>
            </div>
        </div>
    </div>
</div>
@endsection