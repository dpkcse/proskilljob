<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            //
        });
        $appName = config('app.name'); // Retrieve the app name from the config

        DB::table('email_templates')->insert([
            'name' => 'Email Verify',
            'type' => 'email_verify',
            'subject' => 'Verify Your Email',
            'message' => "<div style='box-sizing:border-box;font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:center; background-color:#edf2f7; color:#000; margin:0;padding:20px ;width:100%'><div style='color:#000; margin-bottom: 20px;'><strong>$appName</strong></div><div style='background:#fff; background-color:#fff; color:#718096; font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:left; max-width:600px; margin: 0 auto 20px; border: 1px solid #e5e4e6; border-radius: 10px; padding: 20px;'><p>Thanks for your interest in our newsletter!</p><p>You're one step away</p><h2>Verify your email address</h2><p>to subscribe our newsletter.</p><h3><a href='{verify_email}'>Verify Now</a>&nbsp;</h3><p>Best Regards<br><strong>$appName</strong></p></div><small>© ".date('Y')." $appName. All rights reserved.</small></div>",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->update([
            'email_verification' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            //
        });
    }
};
