<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('banner_type')->default('image'); // image, video
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_file')->nullable();
            $table->string('image_alt')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('status')->default(true);
            // Multilingual and Dynamic fields
            $table->json('translations')->nullable(); // Stores localized fields like line_1, content, etc.
            $table->json('extra_fields')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
