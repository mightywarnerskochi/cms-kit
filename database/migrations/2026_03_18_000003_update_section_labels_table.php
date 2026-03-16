<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('section_labels', function (Blueprint $table) {
            if (!Schema::hasColumn('section_labels', 'status')) {
                $table->boolean('status')->default(true)->after('extra_fields');
            }
            if (!Schema::hasColumn('section_labels', 'display_home')) {
                $table->boolean('display_home')->default(true)->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('section_labels', function (Blueprint $table) {
            $table->dropColumn(['status', 'display_home']);
        });
    }
};
