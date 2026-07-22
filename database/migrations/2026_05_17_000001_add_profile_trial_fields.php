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
        if (! Schema::hasTable('extra_curriculars')) {
            Schema::create('extra_curriculars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('activities');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('extra_curriculars', 'description')) {
            Schema::table('extra_curriculars', function (Blueprint $table) {
                $table->text('description')->nullable()->after('activities');
            });
        }

        if (Schema::hasTable('candidate_education') && ! Schema::hasColumn('candidate_education', 'result_type')) {
            Schema::table('candidate_education', function (Blueprint $table) {
                $table->string('result_type')->nullable()->after('passing_year');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('candidate_education') && Schema::hasColumn('candidate_education', 'result_type')) {
            Schema::table('candidate_education', function (Blueprint $table) {
                $table->dropColumn('result_type');
            });
        }

        if (Schema::hasTable('extra_curriculars') && Schema::hasColumn('extra_curriculars', 'description')) {
            Schema::table('extra_curriculars', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
