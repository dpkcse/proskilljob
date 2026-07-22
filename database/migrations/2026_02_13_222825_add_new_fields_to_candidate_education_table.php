<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('candidate_education', function (Blueprint $table) {
            $table->string('exam_name')->nullable();
            $table->string('degree_name')->nullable();
            $table->string('major_subject')->nullable();
            $table->string('institute_name')->nullable();
            $table->string('result')->nullable();
            $table->string('board')->nullable();
            $table->string('passing_year')->nullable();
            $table->boolean('is_institute_accredited')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('candidate_education', function (Blueprint $table) {
            $table->dropColumn([
                'exam_name',
                'degree_name',
                'major_subject',
                'institute_name',
                'result',
                'board',
                'passing_year',
                'is_institute_accredited'
            ]);
        });
    }
};
