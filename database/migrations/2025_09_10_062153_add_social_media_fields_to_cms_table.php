<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->string('footer_linkedin_link')->nullable()->after('footer_youtube_link');
            $table->string('footer_pinterest_link')->nullable()->after('footer_linkedin_link');
            $table->string('footer_tiktok_link')->nullable()->after('footer_pinterest_link');
            $table->string('footer_whatsapp_link')->nullable()->after('footer_tiktok_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->dropColumn([
                'footer_linkedin_link',
                'footer_pinterest_link',
                'footer_tiktok_link',
                'footer_whatsapp_link',
            ]);
        });
    }
};
