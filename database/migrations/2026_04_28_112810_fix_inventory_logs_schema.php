<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixInventoryLogsSchema extends Migration
{
    public function up()
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            // Drop redundant columns if they exist
            if (Schema::hasColumn('inventory_logs', 'previous_stock')) {
                $table->dropColumn('previous_stock');
            }
            
            if (Schema::hasColumn('inventory_logs', 'new_stock')) {
                $table->dropColumn('new_stock');
            }
            
            // Ensure we have the correct column names
            if (!Schema::hasColumn('inventory_logs', 'old_quantity')) {
                $table->integer('old_quantity')->nullable();
            }
            
            if (!Schema::hasColumn('inventory_logs', 'new_quantity')) {
                $table->integer('new_quantity')->nullable();
            }
            
            // Add missing columns if needed
            if (!Schema::hasColumn('inventory_logs', 'quantity_change')) {
                $table->integer('quantity_change')->nullable();
            }
            
            if (!Schema::hasColumn('inventory_logs', 'reason')) {
                $table->string('reason')->nullable();
            }
            
            if (!Schema::hasColumn('inventory_logs', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable();
            }
        });
        
        // Migrate data if needed (if you had data in previous_stock/new_stock)
        if (Schema::hasColumn('inventory_logs', 'previous_stock') && 
            Schema::hasColumn('inventory_logs', 'old_quantity')) {
            DB::statement('UPDATE inventory_logs SET old_quantity = previous_stock WHERE old_quantity IS NULL AND previous_stock IS NOT NULL');
        }
        
        if (Schema::hasColumn('inventory_logs', 'new_stock') && 
            Schema::hasColumn('inventory_logs', 'new_quantity')) {
            DB::statement('UPDATE inventory_logs SET new_quantity = new_stock WHERE new_quantity IS NULL AND new_stock IS NOT NULL');
        }
    }

    public function down()
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            // This is irreversible to prevent data loss
            $table->integer('previous_stock')->nullable();
            $table->integer('new_stock')->nullable();
        });
    }
}