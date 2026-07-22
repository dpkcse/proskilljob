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
            if (! Schema::hasColumn('candidates', 'father_name')) {
                $table->string('father_name')->nullable()->after('nationality');
            }
            if (! Schema::hasColumn('candidates', 'mother_name')) {
                $table->string('mother_name')->nullable()->after('father_name');
            }
            if (! Schema::hasColumn('candidates', 'religion')) {
                $table->string('religion')->nullable()->after('mother_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $dropColumns = array_filter([
                Schema::hasColumn('candidates', 'father_name') ? 'father_name' : null,
                Schema::hasColumn('candidates', 'mother_name') ? 'mother_name' : null,
                Schema::hasColumn('candidates', 'religion') ? 'religion' : null,
            ]);

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};