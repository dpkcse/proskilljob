<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applied_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('application_group_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Company-less applications legitimately have no application group.
    }
};
