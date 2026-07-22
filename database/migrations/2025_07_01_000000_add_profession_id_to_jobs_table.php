<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('jobs', 'profession_id')) {
                $table->unsignedBigInteger('profession_id')->nullable()->after('category_id');
                $table->foreign('profession_id')->references('id')->on('professions')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'profession_id')) {
                $table->dropForeign(['profession_id']);
                $table->dropColumn('profession_id');
            }
        });
    }
};
