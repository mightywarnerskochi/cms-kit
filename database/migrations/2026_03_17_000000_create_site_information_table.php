<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('site_information', function (Blueprint $table) {
            $table->id();

            // Company Details
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('country')->nullable();
            $table->string('po_box')->nullable();
            $table->string('fax')->nullable();

            // Phone Numbers
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('phone_3')->nullable();
            $table->string('phone_4')->nullable();
            $table->string('whatsapp_number')->nullable();

            // Emails
            $table->string('email_1')->nullable();
            $table->string('email_2')->nullable();
            $table->string('email_3')->nullable();
            $table->string('email_4')->nullable();
            $table->string('receipt_email')->nullable();

            // Legal Content
            $table->longText('privacy_policy')->nullable();
            $table->longText('terms_and_conditions')->nullable();
            $table->longText('disclaimer')->nullable();

            // Visual Assets
            $table->string('logo')->nullable();
            $table->string('logo_alt')->nullable();
            $table->string('footer_logo')->nullable();
            $table->string('footer_logo_alt')->nullable();
            $table->string('favicon')->nullable();
            $table->text('footer_description')->nullable();

            // Social Media
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('snapchat')->nullable();
            $table->string('pinterest')->nullable();
            $table->string('youtube')->nullable();
            $table->string('skype')->nullable();
            $table->string('whatsapp_social')->nullable();
            $table->string('vimeo')->nullable();

            // SEO & Scripts
            $table->string('gtag')->nullable();
            $table->text('custom_head_script')->nullable();
            $table->text('custom_body_script')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_information');
    }
};
