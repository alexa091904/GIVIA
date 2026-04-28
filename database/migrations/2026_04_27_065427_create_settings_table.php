<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('settings')->insert([
            ['key' => 'site_name', 'value' => 'GIVIA Store', 'type' => 'text'],
            ['key' => 'site_email', 'value' => 'admin@givia.com', 'type' => 'email'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'text'],
            ['key' => 'cod_enabled', 'value' => '1', 'type' => 'boolean'],
        ]);
    }
    
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}