<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryLogsTableFresh extends Migration
{
    public function up()
    {
        // Drop existing table if it exists (CAUTION: This deletes data!)
        Schema::dropIfExists('inventory_logs');
        
        // Create fresh clean table
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_change');  // How many units added (+) or removed (-)
            $table->integer('old_quantity');      // Quantity before change
            $table->integer('new_quantity');      // Quantity after change
            $table->string('reason');              // Why the change happened
            $table->unsignedBigInteger('reference_id')->nullable(); // Order ID, User ID, etc.
            $table->text('notes')->nullable();     // Additional details
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['product_id', 'created_at']);
            $table->index('reason');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_logs');
    }
}