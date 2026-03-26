<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_departments', function (Blueprint $table) {
            $table->id();
            $table->json('translations')->nullable();
            $table->json('stats')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('status')->default(true);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->index(['status', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_departments');
    }
};
