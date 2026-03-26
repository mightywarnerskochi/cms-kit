<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_departments', function (Blueprint $table) {
            $table->json('extra_fields')->nullable()->after('stats');
        });
    }

    public function down(): void
    {
        Schema::table('career_departments', function (Blueprint $table) {
            $table->dropColumn('extra_fields');
        });
    }
};
