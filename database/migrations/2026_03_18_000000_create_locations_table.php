<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            
            // Multilingual fields (Title, Address)
            $table->json('translations')->nullable(); 
            
            // Media
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('flag')->nullable();
            $table->string('flag_alt')->nullable();
            
            // Contact info
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('fax')->nullable();
            $table->json('emails')->nullable(); // Stores multiple emails
            
            // Taxonomy
            $table->string('country')->nullable();
            
            // Map

            $table->text('map_link')->nullable();
            
            // Ordering & Status
            $table->integer('order_index')->default(0);
            $table->json('extra_fields')->nullable();
            $table->boolean('status')->default(true);


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('locations');
    }
};
