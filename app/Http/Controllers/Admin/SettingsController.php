<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }
    
    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'required|email',
            'site_phone' => 'nullable|string',
            'site_address' => 'nullable|string',
            'currency' => 'required|string|size:3',
        ]);
        
        foreach ($request->only(['site_name', 'site_email', 'site_phone', 'site_address', 'currency']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        
        Cache::forget('site_settings');
        
        return back()->with('success', 'General settings updated successfully!');
    }
    
    /**
     * Update payment settings
     */
    public function updatePayment(Request $request)
    {
        $request->validate([
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'paypal_client_id' => 'nullable|string',
            'paypal_secret' => 'nullable|string',
            'paypal_mode' => 'nullable|in:sandbox,live',
            'cod_enabled' => 'boolean',
        ]);
        
        foreach ($request->only(['stripe_key', 'stripe_secret', 'paypal_client_id', 'paypal_secret', 'paypal_mode', 'cod_enabled']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        
        return back()->with('success', 'Payment settings updated successfully!');
    }
    
    /**
     * Update shipping settings
     */
    public function updateShipping(Request $request)
    {
        $request->validate([
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'standard_shipping_cost' => 'nullable|numeric|min:0',
            'express_shipping_cost' => 'nullable|numeric|min:0',
        ]);
        
        foreach ($request->only(['free_shipping_threshold', 'standard_shipping_cost', 'express_shipping_cost']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        
        return back()->with('success', 'Shipping settings updated successfully!');
    }
    
    /**
     * Clear application cache
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        
        return back()->with('success', 'Application cache cleared successfully!');
    }
    
    /**
     * Get setting value helper
     */
    public static function get($key, $default = null)
    {
        $settings = Cache::remember('site_settings', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });
        
        return $settings[$key] ?? $default;
    }
}