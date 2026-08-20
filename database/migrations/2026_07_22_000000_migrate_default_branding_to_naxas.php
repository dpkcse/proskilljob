<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update only known platform defaults from the previous brand. Custom site,
     * employer, and tenant values are intentionally never touched.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where('email', 'jobpilot@templatecookie.com')
                ->update(['email' => 'hello@naxas.ai']);
        }

        if (Schema::hasTable('seo_translations')) {
            DB::table('seo_translations')
                ->where('title', 'Welcome To Jobpilot')
                ->update(['title' => 'Welcome To NAXAS']);

            DB::table('seo_translations')
                ->where('description', 'Jobpilot is job portal laravel script designed to create, manage and publish jobs posts. Companies can create their profile and publish jobs posts. Candidate can apply job posts.')
                ->update([
                    'description' => 'NAXAS is a job portal designed to create, manage, and publish job posts. Companies can create profiles and publish jobs, while candidates can apply for them.',
                    'image' => 'frontend/assets/images/logo/logo.svg',
                ]);
        }
    }

    /**
     * No destructive rollback: restored values could overwrite an administrator's branding changes.
     */
    public function down(): void
    {
    }
};
