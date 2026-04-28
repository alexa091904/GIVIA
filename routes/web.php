<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// ============= PUBLIC ROUTES (No login required) =============
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

// ============= CART ROUTES (Accessible without login for guests) =============
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// ============= AUTHENTICATED ROUTES (Login required) =============
Route::middleware(['auth'])->group(function () {
    
    // Checkout route
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    
    // Order routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // User dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// ============= ADMIN ROUTES (Login + Admin role required) =============
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Products Management - Full CRUD
    Route::resource('/products', AdminProductController::class);
    
    // Orders Management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    
    // Inventory Management
    Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/{product}/adjust', [AdminInventoryController::class, 'adjust'])->name('inventory.adjust');
    
    // Users Management - COMPLETE with all routes
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::delete('/users/{user}/clear-cart', [AdminUserController::class, 'clearCart'])->name('users.clear-cart');
    
    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inventory', [AdminReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
    
    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/general', [AdminSettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::post('/settings/payment', [AdminSettingsController::class, 'updatePayment'])->name('settings.payment');
    Route::post('/settings/shipping', [AdminSettingsController::class, 'updateShipping'])->name('settings.shipping');
    Route::post('/settings/cache', [AdminSettingsController::class, 'clearCache'])->name('settings.cache');
});

// ============= AUTH ROUTES (Login, Register, etc.) =============
require __DIR__.'/auth.php';

// ============= TEMPORARY TESTING ROUTES (Remove in production) =============

// Route to create admin user
Route::get('/make-admin', function () {
    // Check if admin already exists
    $adminExists = User::where('role', 'admin')->exists();
    
    if ($adminExists) {
        return "✅ Admin user already exists!<br>
                <a href='/login' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Click here to login</a>";
    }
    
    // Create new admin user
    $user = User::create([
        'name' => 'Administrator',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin'
    ]);
    
    return "✅ Admin user created successfully!<br>
            <strong>Email:</strong> admin@example.com<br>
            <strong>Password:</strong> password<br><br>
            <a href='/login' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Click here to login</a>";
});

// Route to check if views are working
Route::get('/check-views', function() {
    $views = [
        'layouts.app',
        'home',
        'products',
        'product-detail',
        'cart',
        'checkout',
        'order-history',
        'order-details',
        'profile',
        'dashboard',
        'admin.layouts.admin',
        'admin.dashboard',
        'admin.users.index',
        'admin.users.show'
    ];
    
    $results = [];
    foreach ($views as $view) {
        try {
            view($view);
            $results[$view] = '✅ EXISTS';
        } catch (\Exception $e) {
            $results[$view] = '❌ NOT FOUND - ' . $e->getMessage();
        }
    }
    
    // Return simple HTML for debugging
    $html = '<h1>View Checker Results</h1><ul>';
    foreach ($results as $view => $status) {
        $color = str_contains($status, 'EXISTS') ? 'green' : 'red';
        $html .= "<li style='color: {$color}'><strong>{$view}</strong>: {$status}</li>";
    }
    $html .= '</ul><a href="/">Back to Home</a>';
    return $html;
});

// Simple view checker template
Route::get('/simple-check', function() {
    $html = '<h1>View Checker</h1><ul>';
    $views = ['layouts.app', 'home', 'products', 'cart', 'checkout', 'admin.users.show'];
    foreach ($views as $view) {
        try {
            view($view);
            $html .= "<li style='color:green'>✅ {$view}</li>";
        } catch (\Exception $e) {
            $html .= "<li style='color:red'>❌ {$view} - " . $e->getMessage() . "</li>";
        }
    }
    $html .= '</ul><a href="/">Back to Home</a>';
    return $html;
});

// Emergency test route - bypasses controller
Route::get('/test-home', function() {
    return view('home', ['products' => []]);
});

// Test cart route
Route::get('/test-cart', function() {
    return view('cart', [
        'cart' => null,
        'subtotal' => 0,
        'discount' => 0,
        'total' => 0,
        'coupon' => null
    ]);
});

// Test user show route (debugging)
Route::get('/test-user/{id}', function($id) {
    $user = User::findOrFail($id);
    return view('admin.users.show', compact('user'));
})->middleware('auth', 'admin');