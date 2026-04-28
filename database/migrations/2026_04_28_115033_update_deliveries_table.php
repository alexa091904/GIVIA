<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEstimatedDeliveryColumnInDeliveriesTable extends Migration
{
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Check if the old column exists and new column doesn't
            if (Schema::hasColumn('deliveries', 'estimated_delivery_date') && 
                !Schema::hasColumn('deliveries', 'estimated_delivery')) {
                
                $table->renameColumn('estimated_delivery_date', 'estimated_delivery');
            }
        });
    }

    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('deliveries', 'estimated_delivery') && 
                !Schema::hasColumn('deliveries', 'estimated_delivery_date')) {
                
                $table->renameColumn('estimated_delivery', 'estimated_delivery_date');
            }
        });
    }
}