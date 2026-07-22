<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGenderEnumValuesInCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            // First, we need to modify the column to allow the new enum values
            $table->enum('gender', ['male', 'female', 'other', 'машко', 'женско', 'друго'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->change();
        });
    }
}
