<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('job_type');
            $table->string('department');
            $table->string('location');
            $table->string('country');
            $table->string('base')->nullable();
            $table->date('published_date');
            $table->integer('order_index')->default(0);
            $table->boolean('status')->default(true);
            $table->json('translations')->nullable();
            $table->json('metadata')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();

            $table->index(['status', 'order_index']);
            $table->index('published_date');
            $table->index('job_type');
            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
