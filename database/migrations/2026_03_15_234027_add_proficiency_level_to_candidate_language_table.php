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
        Schema::table('candidate_language', function (Blueprint $table) {
            if (! Schema::hasColumn('candidate_language', 'proficiency_level')) {
                $table->enum('proficiency_level', ['basic', 'intermediate', 'fluent', 'native'])
                    ->default('basic')
                    ->after('candidate_language_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_language', function (Blueprint $table) {
            if (Schema::hasColumn('candidate_language', 'proficiency_level')) {
                $table->dropColumn('proficiency_level');
            }
        });
    }
};