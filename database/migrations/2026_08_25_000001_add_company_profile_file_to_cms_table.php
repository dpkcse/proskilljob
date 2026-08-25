<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cms', 'company_profile_file')) {
            Schema::table('cms', function (Blueprint $table) {
                $table->string('company_profile_file')->nullable()->after('footer_trade_license_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cms', 'company_profile_file')) {
            Schema::table('cms', function (Blueprint $table) {
                $table->dropColumn('company_profile_file');
            });
        }
    }
};
