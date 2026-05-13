<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('url_miss_logs', function (Blueprint $table) {
            $table->id();
            $table->string('path', 2048)->unique();
            $table->unsignedInteger('hit_count')->default(0);
            $table->text('last_referer')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_miss_logs');
    }
};
