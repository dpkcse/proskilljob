<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $appName = config('app.name');
        DB::table('email_templates')->insert([
            'name' => 'Profile Verified Successfully Notification',
            'type' => 'profile_verified',
            'subject' => 'Profile Verified Successfully',
            'message' => "<div style='box-sizing:border-box;font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:center; background-color:#edf2f7; color:#000; margin:0;padding:20px ;width:100%'><div style='color:#000; margin-bottom: 20px;'><strong>$appName</strong></div><div style='background:#fff; background-color:#fff; color:#718096; font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:left; max-width:600px; margin: 0 auto 20px; border: 1px solid #e5e4e6; border-radius: 10px; padding: 20px;'><p>Dear {user_name}</p><p>We are pleased to inform you that your profile on $appName has been successfully verified.</p><p>With a verified profile, you can now take full advantage of the enhanced features and opportunities on our platform.</p><p>Thank you for your cooperation throughout the verification process. We wish you the best in your job search.</p><p>Best Regards<br><strong>$appName</strong></p></div><small>© ".date('Y')." $appName. All rights reserved.</small></div>",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('email_templates')->insert([
            'name' => 'Document Verification Submitted Notification',
            'type' => 'verification_document_submitted',
            'subject' => 'New Document Verification Submission',
            'message' => "<div style='box-sizing:border-box;font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:center; background-color:#edf2f7; color:#000; margin:0;padding:20px ;width:100%'><div style='color:#000; margin-bottom: 20px;'><strong>$appName</strong></div><div style='background:#fff; background-color:#fff; color:#718096; font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:left; max-width:600px; margin: 0 auto 20px; border: 1px solid #e5e4e6; border-radius: 10px; padding: 20px;'><p>Hello Admin,</p><p>A new document verification submission has been received on <strong>$appName</strong> by <strong>{user_name}</strong>.</p><p><a href='{document_url}'>View Document</a></p><p>Please log in to the admin dashboard to process the request.</p><p>Thank you for maintaining the security and credibility of our platform.</p><p>Best Regards<br><strong>$appName</strong></p></div><small>© ".date('Y')." $appName. All rights reserved.</small></div>",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('email_templates')->insert([
            'name' => 'Apply Job Notification',
            'type' => 'apply_job_notification',
            'subject' => 'Applied for {job_title} at {company_name}',
            'message' => "<div style='box-sizing:border-box;font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:center; background-color:#edf2f7; color:#000; margin:0;padding:20px ;width:100%'><div style='color:#000; margin-bottom: 20px;'><strong>$appName</strong></div><div style='background:#fff; background-color:#fff; color:#718096; font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:left; max-width:600px; margin: 0 auto 20px; border: 1px solid #e5e4e6; border-radius: 10px; padding: 20px;'><p>Hello {company_name},</p><p>{user_name} has applied for the job position <strong>{job_title}</strong>.</p><p>Please log in to your dashboard to review the application.</p><p>Best Regards<br><strong>$appName</strong></p></div><small>© ".date('Y')." $appName. All rights reserved.</small></div>",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('email_templates')->insert([
            'name' => 'Job Waiting For Edit Approval Notification',
            'type' => 'job_waiting_for_edit_approval',
            'subject' => 'Job "{job_title}" at {company_name} is waiting for admin edit approval',
            'message' => "<div style='box-sizing:border-box;font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:center; background-color:#edf2f7; color:#000; margin:0;padding:20px ;width:100%'><div style='color:#000; margin-bottom: 20px;'><strong>$appName</strong></div><div style='background:#fff; background-color:#fff; color:#718096; font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:left; max-width:600px; margin: 0 auto 20px; border: 1px solid #e5e4e6; border-radius: 10px; padding: 20px;'><p>Hello Admin,</p><p>The job <strong>{job_title}</strong> at <strong>{company_name}</strong> has been edited and is waiting for your approval.</p><p>Please log in to the admin dashboard to review and approve the changes.</p><p>Best Regards<br><strong>$appName</strong></p></div><small>© ".date('Y')." $appName. All rights reserved.</small></div>",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('email_templates')->insert([
            'name' => 'Payment Mark Paid Notification',
            'type' => 'payment_mark_paid_notification',
            'subject' => 'Your payment has been marked as paid',
            'message' => "<div style='box-sizing:border-box;font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:center; background-color:#edf2f7; color:#000; margin:0;padding:20px ;width:100%'><div style='color:#000; margin-bottom: 20px;'><strong>$appName</strong></div><div style='background:#fff; background-color:#fff; color:#718096; font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:left; max-width:600px; margin: 0 auto 20px; border: 1px solid #e5e4e6; border-radius: 10px; padding: 20px;'><p>Hello {user_name},</p><p>Your payment has been marked as paid. You can now start working on your job.</p><p>Thank you for using our platform.</p><p>Best Regards<br><strong>$appName</strong></p></div><small>© ".date('Y')." $appName. All rights reserved.</small></div>",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('email_templates')->insert([
            'name' => 'Password Reset Notification',
            'type' => 'password_reset',
            'subject' => 'Password Reset Request',
            'message' => "<div style='box-sizing:border-box;font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:center; background-color:#edf2f7; color:#000; margin:0;padding:20px ;width:100%'><div style='color:#000; margin-bottom: 20px;'><strong>$appName</strong></div><div style='background:#fff; background-color:#fff; color:#718096; font-family:Helvetica,Arial,sans-serif; font-size:16px; text-align:left; max-width:600px; margin: 0 auto 20px; border: 1px solid #e5e4e6; border-radius: 10px; padding: 20px;'><p>Hello {user_name},</p><p>Your password reset code is: <strong>{reset_token}</strong></p><p>If you did not request a password reset, please ignore this email.</p><p>Thank you for using our application!</p><p>Best Regards<br><strong>$appName</strong></p></div><small>© ".date('Y')." $appName. All rights reserved.</small></div>",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('email_templates')->where('type', 'profile_verified')->delete();
        DB::table('email_templates')->where('type', 'verification_document_submitted')->delete();
        DB::table('email_templates')->where('type', 'apply_job_notification')->delete();
        DB::table('email_templates')->where('type', 'job_waiting_for_edit_approval')->delete();
        DB::table('email_templates')->where('type', 'payment_mark_paid_notification')->delete();
        DB::table('email_templates')->where('type', 'password_reset')->delete();
    }
};
