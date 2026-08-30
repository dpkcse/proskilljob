<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('candidates', 'nid_birth_registration_no')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->string('nid_birth_registration_no', 100)->nullable()->after('nationality');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('candidates', 'nid_birth_registration_no')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->dropColumn('nid_birth_registration_no');
            });
        }
    }
};
