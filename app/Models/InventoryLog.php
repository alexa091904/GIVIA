<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_change',  // Positive = added, Negative = removed
        'old_quantity',
        'new_quantity',
        'reason',
        'reference_id',
        'notes'
    ];
    
    protected $casts = [
        'quantity_change' => 'integer',
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    // Accessor for formatted change display
    public function getFormattedChangeAttribute()
    {
        if ($this->quantity_change > 0) {
            return "+{$this->quantity_change}";
        }
        return (string) $this->quantity_change;
    }
    
    // Accessor for change type
    public function getChangeTypeAttribute()
    {
        if ($this->quantity_change > 0) return 'addition';
        if ($this->quantity_change < 0) return 'removal';
        return 'no_change';
    }
    
    // Scope for recent logs
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    
    // Scope for specific reason
    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }
}