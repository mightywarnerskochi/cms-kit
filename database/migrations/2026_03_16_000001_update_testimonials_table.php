<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'section_image')) {
                $table->dropColumn('section_image');
            }
            // We keep 'translations' but it will now only hold name, designation, and content
            // We don't necessarily need to wipe the old section data from it yet,
            // but the controller will stop writing section data to this table.
        });
    }

    public function down()
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('section_image')->nullable();
        });
    }
};
