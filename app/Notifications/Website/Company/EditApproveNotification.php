<?php

namespace App\Notifications\Website\Company;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EditApproveNotification extends Notification
{
    use Queueable;

    public $user;

    public $job;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $job)
    {
        $this->user = $user;
        $this->job = $job;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Send email using the template 'job_waiting_for_edit_approval'
        $type = 'job_waiting_for_edit_approval';
        $formatted_mail_data = getFormattedTextByType($type, [
            'user_name' => $this->user->name,
            'job_title' => $this->job->title,
            'company_name' => $this->job->company->name,
        ]);
        $subject = $formatted_mail_data['subject'];
        $message = $formatted_mail_data['message'];

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action(__('view'), route('website.job.details', $this->job->slug))
            ->view('mails.email-template', ['content' => $message]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => __('your_job_has_been_edited_and_waiting_for_admin_approval_your_changes'),
            'url' => route('website.job.details', $this->job->slug),
        ];
    }
}
