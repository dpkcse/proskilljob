<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminEmailTemplateRequest;
use App\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    const EMAIL_TYPES = [
        'new_edited_job_available',
        'new_job_available',
        'new_plan_purchase',
        'new_user_registered',
        'plan_purchase',
        'new_pending_candidate',
        'new_candidate',
        'new_company_pending',
        'new_company',
        'update_company_pass',
        'verify_subscription_notification',
        'email_verify',
        'profile_verified',
        'verification_document_submitted',
        'apply_job_notification',
        'job_waiting_for_edit_approval',
        'payment_mark_paid_notification',
        'password_reset',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $email_templates = EmailTemplate::all();

            return view('backend.settings.pages.email-template', compact('email_templates'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function save(AdminEmailTemplateRequest $request)
    {
        try {
            $email_template_data = [
                'subject' => $request->subject,
                'message' => $request->message,
            ];

            if (! empty($email_template_data['name'])) {
                $email_template_data['name'] = $request->name;
            }

            $email_template = ! empty($request->id) ? EmailTemplate::find($request->id) : null;

            if ($email_template) {
                $email_template = $email_template->update($email_template_data);
            } else {
                $email_template_data['type'] = $request->type;
                $email_template = EmailTemplate::create($email_template_data);
            }

            if ($email_template) {
                return back()->with('success', __('Email template saved!'));
            }

            return back()->with('error', __('Email template save failed!'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * format `$message` by `$type`
     *
     * @param  string  $message
     * @return mixed formatted data
     */
    public static function getFormattedTextByType(string $type = 'verify_subscription_notification', $data = null)
    {
        // try {

        $type_data = self::getDataByType($type, $data);
        $formatter = self::getFormatterByType($type, $type_data);
        $email_template = EmailTemplate::where('type', $type)->first();
        $subject = optional($email_template)->subject ?? '';
        $message = optional($email_template)->message ?? '';
        $formatted_data = [
            'subject' => html_entity_decode(str_replace($formatter['search'], $formatter['replace'], $subject)),
            'message' => html_entity_decode(str_replace($formatter['search'], $formatter['replace'], $message)),
        ];

        return $formatted_data;
        // } catch (\Exception $e) {
        //     flashError('An error occurred: '.$e->getMessage());

        //     return back();
        // }
    }

    /**
     * get data by type to be replaced by flags
     *
     * @param  string  $type  type of email template
     * @param  mixed  $data  any data passed
     * @return array
     */
    public static function getDataByType($type, $data = null)
    {
        try {
            $return_data = [];

            // if the type is among the following, key and value is the same
            if (in_array($type, self::EMAIL_TYPES)) {
                $return_data = $data;
            }

            return $return_data;
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * format flags with data
     *
     * @param  ?mixed  $data
     * @return array array with search and replace data
     */
    public static function getFormatterByType(string $type, $data = null)
    {
        try {
            // Define the flags for each type
            $typeFlags = [
                'new_user' => ['{user_name}', '{company_name}'],
                'new_edited_job_available' => ['{admin_name}'],
                'new_job_available' => ['{admin_name}'],
                'new_plan_purchase' => ['{admin_name}', '{user_name}', '{plan_label}'],
                'new_user_registered' => ['{admin_name}', '{user_role}'],
                'plan_purchase' => ['{user_name}', '{plan_type}'],
                'new_pending_candidate' => ['{user_name}', '{user_email}', '{user_password}'],
                'new_candidate' => ['{user_name}', '{user_email}', '{user_password}'],
                'new_company_pending' => ['{user_name}', '{user_email}', '{user_password}'],
                'new_company' => ['{user_name}', '{user_email}', '{user_password}'],
                'update_company_pass' => ['{user_name}', '{user_email}', '{password}', '{account_type}'],
                'verify_subscription_notification' => ['{verify_subscription}'],
                'email_verify' => ['{verify_email}'],
                // new email templates
                'profile_verified' => ['{user_name}'],
                'verification_document_submitted' => ['{user_name}', '{document_url}'],
                'apply_job_notification' => ['{company_name}', '{user_name}', '{job_title}'],
                'job_waiting_for_edit_approval' => ['{admin_name}', '{company_name}', '{job_title}'],
                'payment_mark_paid_notification' => ['{user_name}'],
                'password_reset' => ['{user_name}', '{reset_token}'],
            ];

            $format = [
                'search' => [],
                'replace' => [],
            ];

            if (isset($typeFlags[$type])) {
                $format['search'] = $typeFlags[$type];
                if ($data !== null) {
                    foreach ($typeFlags[$type] as $flag) {
                        // Clean the flag to get the key
                        // e.g., {admin_name} becomes admin_name
                        $key = trim($flag, '{}');
                        $format['replace'][] = $data[$key] ?? '';
                    }
                }
            }

            return $format;
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }
}
