<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\CategoryController;

// ============= PUBLIC ROUTES (No authentication needed) =============
// Rate limiting applied to prevent abuse
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
});

// ============= PROTECTED ROUTES (Requires authentication) =============
Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    
    // Cart Routes - Full CRUD operations
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::get('/summary', [CartController::class, 'summary'])->name('cart.summary');
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::put('/update/{itemId}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
        
        // Coupon routes
        Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');
        Route::delete('/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.remove-coupon');
    });
    
    // Order Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/', [OrderController::class, 'store'])->middleware('throttle:5,1')->name('orders.store');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });
    
    // Payment Routes - Stricter rate limiting
    Route::prefix('payments')->group(function () {
        Route::post('/process/{order}', [PaymentController::class, 'process'])->middleware('throttle:3,1')->name('payments.process');
        Route::get('/status/{order}', [PaymentController::class, 'status'])->name('payments.status');
        Route::post('/refund/{order}', [PaymentController::class, 'refund'])->middleware('throttle:2,1')->name('payments.refund');
    });
    
    // Delivery Routes
    Route::prefix('delivery')->group(function () {
        Route::get('/track/{order}', [DeliveryController::class, 'track'])->name('delivery.track');
    });
});

// ============= ADMIN ROUTES (Requires authentication + admin role) =============
Route::middleware(['auth:sanctum', 'admin', 'throttle:200,1'])->prefix('admin')->group(function () {
    
    // Category Management
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    
    // Alternative explicit routes (if apiResource doesn't work)
    // Route::post('/categories', [CategoryController::class, 'store']);
    // Route::put('/categories/{id}', [CategoryController::class, 'update']);
    // Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    // Product Management (Admin)
    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store'])->name('admin.products.store');
        Route::put('/{id}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    });
    
    // Order Management (Admin)
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'adminIndex'])->name('admin.orders.index');
        Route::put('/{order}/status', [OrderController::class, 'adminUpdateStatus'])->name('admin.orders.status');
    });
    
    // Inventory Management (Admin)
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('admin.inventory.index');
        Route::post('/{product}/adjust', [InventoryController::class, 'adjust'])->name('admin.inventory.adjust');
    });
    
    // Reports (Admin)
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('admin.reports.sales');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('admin.reports.inventory');
        Route::get('/export', [ReportController::class, 'export'])->name('admin.reports.export');
    });
});

// ============= USER INFO ROUTE =============
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
        'role' => $request->user()->role,
        'created_at' => $request->user()->created_at,
    ]);
});

// ============= GUEST CART ROUTES (No auth required) =============
// These allow guests to add items to cart before logging in
Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/guest/cart', [CartController::class, 'index']);
    Route::post('/guest/cart/add', [CartController::class, 'add']);
    Route::put('/guest/cart/update/{itemId}', [CartController::class, 'update']);
    Route::delete('/guest/cart/remove/{itemId}', [CartController::class, 'remove']);
    Route::delete('/guest/cart/clear', [CartController::class, 'clear']);
});

// ============= HEALTH CHECK =============
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'environment' => app()->environment()
    ]);
});