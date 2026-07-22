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
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('experience_area')->nullable();
            $table->string('business_area')->nullable();
            $table->string('business_area_other')->nullable();
            $table->text('experience_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'experience_area',
                'business_area',
                'business_area_other',
                'experience_description',
            ]);
        });
    }
};
