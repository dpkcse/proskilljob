<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cms', 'footer_address')) {
            Schema::table('cms', function (Blueprint $table) {
                $table->text('footer_address')->nullable()->after('footer_phone_no');
            });
        }

        if (! Schema::hasColumn('cms', 'footer_trade_license_number')) {
            Schema::table('cms', function (Blueprint $table) {
                $table->string('footer_trade_license_number')->nullable()->after('footer_address');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->dropColumn(['footer_trade_license_number', 'footer_address']);
        });
    }
};
