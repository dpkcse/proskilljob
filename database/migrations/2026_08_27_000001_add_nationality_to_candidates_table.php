<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('candidates', 'nationality')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->string('nationality')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('candidates', 'nationality')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->dropColumn('nationality');
            });
        }
    }
};
