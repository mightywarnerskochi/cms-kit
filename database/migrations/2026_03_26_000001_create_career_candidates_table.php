<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('apply_for')->nullable();
            $table->string('experience')->nullable();
            $table->string('designation')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->text('additional_information')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('privacy')->default(false);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->index('submitted_at');
            $table->index('apply_for');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_candidates');
    }
};
