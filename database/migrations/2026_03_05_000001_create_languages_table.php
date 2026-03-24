<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique(); // e.g., 'en', 'ar'
            $table->string('flag_image')->nullable();
            $table->string('flag_alt')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Insert default English
        \DB::table('languages')->insert([
            'name' => 'English',
            'code' => 'en',
            'is_default' => true,
            'status' => true,
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('languages');
    }
};
