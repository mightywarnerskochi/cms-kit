<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('section_labels', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique();
            $table->json('translations')->nullable(); // Stores title, sub_heading_1, sub_heading_2 per language
            $table->string('section_image')->nullable();
            $table->string('section_image_alt')->nullable();
            $table->string('banner')->nullable();
            $table->string('banner_alt')->nullable();
            $table->json('description')->nullable();
            $table->json('extra_fields')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('section_labels');
    }
};
