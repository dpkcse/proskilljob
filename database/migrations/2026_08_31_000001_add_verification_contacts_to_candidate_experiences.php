<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_experiences', function (Blueprint $table) {
            if (! Schema::hasColumn('candidate_experiences', 'supervisor')) {
                $table->string('supervisor')->nullable()->after('responsibilities');
            }
            if (! Schema::hasColumn('candidate_experiences', 'hr_contact_number')) {
                $table->string('hr_contact_number', 50)->nullable()->after('supervisor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidate_experiences', function (Blueprint $table) {
            $columns = collect(['supervisor', 'hr_contact_number'])
                ->filter(fn ($column) => Schema::hasColumn('candidate_experiences', $column))
                ->all();
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
