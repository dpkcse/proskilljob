<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some production dumps carry an unintended ON UPDATE CURRENT_TIMESTAMP
        // clause on this column, which expires a registration whenever any mail
        // delivery metadata is updated.
        Schema::table('pending_users', function (Blueprint $table) {
            $table->timestamp('expires_at')->change();
        });

        Schema::table('pending_users', function (Blueprint $table) {
            $table->timestamp('last_verification_sent_at')->nullable()->after('expires_at');
            $table->unsignedInteger('verification_send_attempts')->default(0)->after('last_verification_sent_at');
            $table->text('verification_last_error')->nullable()->after('verification_send_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('pending_users', function (Blueprint $table) {
            $table->dropColumn([
                'last_verification_sent_at',
                'verification_send_attempts',
                'verification_last_error',
            ]);
        });
    }
};
