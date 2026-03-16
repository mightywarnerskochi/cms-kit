<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            
            // Multilingual fields
            $table->json('translations')->nullable(); // Stores { 'en': { 'question': '...', 'answer': '...' }, 'ar': { ... } }
            
            // Shared fields
            $table->integer('order_index')->default(0);
            $table->boolean('status')->default(true);
            
            // Polymorphic fields for contextual FAQs (e.g., product/service specific)
            $table->string('faqable_type')->nullable();
            $table->unsignedBigInteger('faqable_id')->nullable();
            $table->index(['faqable_type', 'faqable_id']);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('faqs');
    }
};
