<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('company_registration_number')->nullable()->unique()->after('website');
        });

        Schema::table('pending_users', function (Blueprint $table) {
            $table->string('company_registration_number')->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('pending_users', function (Blueprint $table) {
            $table->dropUnique(['company_registration_number']);
            $table->dropColumn('company_registration_number');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['company_registration_number']);
            $table->dropColumn('company_registration_number');
        });
    }
};
