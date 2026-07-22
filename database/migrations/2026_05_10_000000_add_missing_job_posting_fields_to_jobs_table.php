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
            if (! Schema::hasColumn('jobs', 'profession_id')) {
                $table->unsignedBigInteger('profession_id')->nullable()->after('category_id');
            }

            if (! Schema::hasColumn('jobs', 'salary_mode')) {
                $table->enum('salary_mode', ['range', 'custom'])->default('range')->after('max_salary');
            }

            if (! Schema::hasColumn('jobs', 'custom_salary')) {
                $table->string('custom_salary')->nullable()->after('salary_mode');
            }

            if (! Schema::hasColumn('jobs', 'job_start')) {
                $table->date('job_start')->nullable()->after('deadline');
            }

            if (! Schema::hasColumn('jobs', 'job_end')) {
                $table->date('job_end')->nullable()->after('job_start');
            }

            if (! Schema::hasColumn('jobs', 'gender')) {
                $table->enum('gender', ['male', 'female'])->nullable()->after('job_type_id');
            }

            if (! Schema::hasColumn('jobs', 'min_age')) {
                $table->unsignedTinyInteger('min_age')->nullable()->after('gender');
            }

            if (! Schema::hasColumn('jobs', 'max_age')) {
                $table->unsignedTinyInteger('max_age')->nullable()->after('min_age');
            }

            if (! Schema::hasColumn('jobs', 'experience_area')) {
                $table->string('experience_area')->nullable()->after('max_age');
            }

            if (! Schema::hasColumn('jobs', 'business_area')) {
                $table->string('business_area')->nullable()->after('experience_area');
            }

            if (! Schema::hasColumn('jobs', 'business_area_other')) {
                $table->string('business_area_other')->nullable()->after('business_area');
            }

            if (! Schema::hasColumn('jobs', 'experience_description')) {
                $table->text('experience_description')->nullable()->after('business_area_other');
            }

            if (! Schema::hasColumn('jobs', 'required_degrees')) {
                $table->json('required_degrees')->nullable()->after('experience_description');
            }

            if (! Schema::hasColumn('jobs', 'required_degrees_other')) {
                $table->string('required_degrees_other')->nullable()->after('required_degrees');
            }

            if (! Schema::hasColumn('jobs', 'preferred_institutions')) {
                $table->json('preferred_institutions')->nullable()->after('required_degrees_other');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
