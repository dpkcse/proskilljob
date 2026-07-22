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
        Schema::create('candidate_education_skill', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('candidate_education_id');
            $table->unsignedBigInteger('skill_id');

            $table->timestamps();

            $table->foreign('candidate_education_id')
                ->references('id')
                ->on('candidate_education')
                ->onDelete('cascade');

            $table->foreign('skill_id')
                ->references('id')
                ->on('skills')
                ->onDelete('cascade');

            $table->unique(['candidate_education_id', 'skill_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_education_skill');
    }

};
