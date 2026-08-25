<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('companies')
            ->select('id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('application_groups')
                    ->whereColumn('application_groups.company_id', 'companies.id')
                    ->where('application_groups.is_deleteable', false);
            })
            ->orderBy('id')
            ->chunkById(500, function ($companies) use ($now) {
                $groups = $companies->map(fn ($company) => [
                    'company_id' => $company->id,
                    'name' => 'No Group',
                    'order' => 1,
                    'is_deleteable' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('application_groups')->insert($groups);
            });
    }

    public function down(): void
    {
        // Existing application records may reference these groups, so rollback is intentionally non-destructive.
    }
};
