<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('tracking_number')->nullable();
            $table->string('current_location')->nullable();
            $table->enum('status', ['preparing', 'shipped', 'out_for_delivery', 'delivered'])->default('preparing');
            $table->text('tracking_info')->nullable();
            $table->json('updates_history')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};