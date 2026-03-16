<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            
            // Core fields
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            
            // Context fields
            $table->string('page_url')->nullable();
            $table->string('page_source')->nullable();
            
            // Content
            $table->text('message')->nullable();
            
            // Dynamic/Additional fields
            $table->json('extra_fields')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enquiries');
    }
};
