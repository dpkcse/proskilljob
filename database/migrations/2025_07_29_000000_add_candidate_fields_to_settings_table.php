<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('candidate_gender_active')->default(true);
            $table->boolean('candidate_birth_date_active')->default(true);
            $table->boolean('candidate_marital_status_active')->default(true);
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('candidate_gender_active');
            $table->dropColumn('candidate_birth_date_active');
            $table->dropColumn('candidate_marital_status_active');
        });
    }
};
