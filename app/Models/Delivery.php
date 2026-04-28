<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $table = 'deliveries';
    
    protected $fillable = [
        'order_id',
        'status',
        'tracking_number',
        'estimated_delivery',  // Use this if that's your column name
        'estimated_delivery_date',  // Use this if that's your column name
        'current_location',
        'updates_history'
    ];
    
    protected $casts = [
        'updates_history' => 'array',
        'estimated_delivery' => 'datetime',
        'estimated_delivery_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // Relationship
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    // Accessor to always have 'estimated_delivery' available
    public function getEstimatedDeliveryAttribute()
    {
        return $this->attributes['estimated_delivery'] ?? $this->attributes['estimated_delivery_date'] ?? null;
    }
    
    // Mutator to set the correct column
    public function setEstimatedDeliveryAttribute($value)
    {
        if (Schema::hasColumn('deliveries', 'estimated_delivery')) {
            $this->attributes['estimated_delivery'] = $value;
        } elseif (Schema::hasColumn('deliveries', 'estimated_delivery_date')) {
            $this->attributes['estimated_delivery_date'] = $value;
        }
    }
}