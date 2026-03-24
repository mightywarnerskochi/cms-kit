<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->date('published_at');
            $table->string('feature_image')->nullable();
            $table->string('feature_image_alt')->nullable();
            $table->string('detail_image')->nullable();
            $table->string('detail_image_alt')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('banner_alt')->nullable();
            $table->string('image_3')->nullable();
            $table->string('image_3_alt')->nullable();
            $table->string('image_4')->nullable();
            $table->string('image_4_alt')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('status')->default(true);
            $table->json('translations')->nullable();
            $table->json('extra_fields')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blogs');
    }
};
