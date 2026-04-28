<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Sales reports dashboard
     */
    public function index()
    {
        // Date range for reports
        $startDate = request()->get('start_date', now()->subDays(30));
        $endDate = request()->get('end_date', now());
        
        // Daily sales
        $dailySales = Order::whereBetween('created_at', [$startDate, $endDate])
                           ->where('status', '!=', 'cancelled')
                           ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
                           ->groupBy('date')
                           ->orderBy('date', 'desc')
                           ->limit(30)
                           ->get();
        
        // Top products
        $topProducts = DB::table('order_items')
                         ->join('products', 'order_items.product_id', '=', 'products.id')
                         ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
                         ->groupBy('products.id', 'products.name')
                         ->orderBy('total_sold', 'desc')
                         ->limit(10)
                         ->get();
        
        // Monthly stats
        $monthlyRevenue = Order::where('status', '!=', 'cancelled')
                               ->whereYear('created_at', now()->year)
                               ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_amount) as total'))
                               ->groupBy('month')
                               ->get();
        
        // Summary stats
        $summary = [
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
            'average_order_value' => Order::where('status', '!=', 'cancelled')->avg('total_amount'),
            'total_products_sold' => DB::table('order_items')->sum('quantity'),
            'total_customers' => User::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];
        
        return view('admin.reports.index', compact(
            'dailySales',
            'topProducts',
            'monthlyRevenue',
            'summary',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Inventory report
     */
    public function inventory()
    {
        $lowStock = Product::where('stock', '<=', 10)->get();
        $outOfStock = Product::where('stock', '=', 0)->get();
        $topSelling = DB::table('order_items')
                         ->join('products', 'order_items.product_id', '=', 'products.id')
                         ->select('products.name', 'products.stock', DB::raw('SUM(order_items.quantity) as sold'))
                         ->groupBy('products.id', 'products.name', 'products.stock')
                         ->orderBy('sold', 'desc')
                         ->limit(10)
                         ->get();
        
        $inventoryLogs = \App\Models\InventoryLog::with('product')
                         ->orderBy('created_at', 'desc')
                         ->limit(50)
                         ->get();
        
        return view('admin.reports.inventory', compact('lowStock', 'outOfStock', 'topSelling', 'inventoryLogs'));
    }
    
    /**
     * Export sales report to CSV
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());
        
        $orders = Order::with('user')
                       ->whereBetween('created_at', [$startDate, $endDate])
                       ->get();
        
        $filename = "sales_report_{$startDate}_to_{$endDate}.csv";
        
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Order #', 'Customer', 'Date', 'Total', 'Status']);
        
        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->order_number,
                $order->user->name ?? 'Guest',
                $order->created_at->format('Y-m-d'),
                $order->total_amount,
                $order->status
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}