@extends('admin.layouts.admin')

@section('page-title', 'System Settings')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#general">General Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#payment">Payment Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#shipping">Shipping Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#system">System</a>
            </li>
        </ul>
        
        <div class="tab-content mt-4">
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active" id="general">
                <div class="card">
                    <div class="card-header">
                        <h5>General Store Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.settings.general') }}">
                            @csrf
                            <div class="mb-3">
                                <label>Store Name</label>
                                <input type="text" name="site_name" class="form-control" 
                                       value="{{ $settings['site_name'] ?? 'GIVIA Store' }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Store Email</label>
                                <input type="email" name="site_email" class="form-control" 
                                       value="{{ $settings['site_email'] ?? 'admin@givia.com' }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Store Phone</label>
                                <input type="text" name="site_phone" class="form-control" 
                                       value="{{ $settings['site_phone'] ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label>Store Address</label>
                                <textarea name="site_address" class="form-control" rows="3">{{ $settings['site_address'] ?? '' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label>Currency (3-letter code)</label>
                                <input type="text" name="currency" class="form-control" 
                                       value="{{ $settings['currency'] ?? 'USD' }}" maxlength="3" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save General Settings</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Payment Settings Tab -->
            <div class="tab-pane fade" id="payment">
                <div class="card">
                    <div class="card-header">
                        <h5>Payment Gateway Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.settings.payment') }}">
                            @csrf
                            <h6>Stripe Configuration</h6>
                            <div class="mb-3">
                                <label>Stripe Publishable Key</label>
                                <input type="text" name="stripe_key" class="form-control" 
                                       value="{{ $settings['stripe_key'] ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label>Stripe Secret Key</label>
                                <input type="password" name="stripe_secret" class="form-control" 
                                       value="{{ $settings['stripe_secret'] ?? '' }}">
                            </div>
                            
                            <hr>
                            
                            <h6>PayPal Configuration</h6>
                            <div class="mb-3">
                                <label>PayPal Client ID</label>
                                <input type="text" name="paypal_client_id" class="form-control" 
                                       value="{{ $settings['paypal_client_id'] ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label>PayPal Secret</label>
                                <input type="password" name="paypal_secret" class="form-control" 
                                       value="{{ $settings['paypal_secret'] ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label>PayPal Mode</label>
                                <select name="paypal_mode" class="form-control">
                                    <option value="sandbox" {{ ($settings['paypal_mode'] ?? '') == 'sandbox' ? 'selected' : '' }}>Sandbox (Test)</option>
                                    <option value="live" {{ ($settings['paypal_mode'] ?? '') == 'live' ? 'selected' : '' }}>Live (Production)</option>
                                </select>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label class="d-block">
                                    <input type="checkbox" name="cod_enabled" value="1" 
                                           {{ ($settings['cod_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    Enable Cash on Delivery (COD)
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Payment Settings</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Settings Tab -->
            <div class="tab-pane fade" id="shipping">
                <div class="card">
                    <div class="card-header">
                        <h5>Shipping Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.settings.shipping') }}">
                            @csrf
                            <div class="mb-3">
                                <label>Free Shipping Threshold ($)</label>
                                <input type="number" step="0.01" name="free_shipping_threshold" class="form-control" 
                                       value="{{ $settings['free_shipping_threshold'] ?? 50 }}" placeholder="50">
                                <small>Orders above this amount get free shipping</small>
                            </div>
                            <div class="mb-3">
                                <label>Standard Shipping Cost ($)</label>
                                <input type="number" step="0.01" name="standard_shipping_cost" class="form-control" 
                                       value="{{ $settings['standard_shipping_cost'] ?? 5.99 }}">
                            </div>
                            <div class="mb-3">
                                <label>Express Shipping Cost ($)</label>
                                <input type="number" step="0.01" name="express_shipping_cost" class="form-control" 
                                       value="{{ $settings['express_shipping_cost'] ?? 12.99 }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Shipping Settings</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- System Tab -->
            <div class="tab-pane fade" id="system">
                <div class="card">
                    <div class="card-header">
                        <h5>System Maintenance</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>System Information:</strong><br>
                            Laravel Version: {{ app()->version() }}<br>
                            PHP Version: {{ phpversion() }}<br>
                            Environment: {{ app()->environment() }}
                        </div>
                        
                        <form method="POST" action="{{ route('admin.settings.cache') }}">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Clear all cache?')">
                                Clear Application Cache
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection