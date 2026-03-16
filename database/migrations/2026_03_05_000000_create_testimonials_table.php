<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            // Core fields (untranslated/shared)
            $table->string('section_image')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->integer('rating')->default(5);
            $table->integer('order_index')->default(0);
            $table->boolean('status')->default(true);

            // Multilingual and Dynamic fields
            $table->json('translations')->nullable(); // Stores { 'en': { 'title': '...', 'content': '...' }, 'ar': { ... } }
            $table->json('extra_fields')->nullable(); // Stores { 'sub_heading_3': '...' }

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimonials');
    }
};
