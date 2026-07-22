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
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('passport_no')->nullable()->after('bio');
            $table->date('passport_issue_date')->nullable()->after('passport_no');
            $table->string('passport_place_of_issue')->nullable()->after('passport_issue_date');
            $table->date('passport_expiry_date')->nullable()->after('passport_place_of_issue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'passport_no',
                'passport_issue_date',
                'passport_place_of_issue',
                'passport_expiry_date',
            ]);
        });
    }
};