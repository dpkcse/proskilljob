<?php

namespace App\Notifications\Website\Candidate;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplyJobNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $user;

    public $company;

    public $job;

    public function __construct($user, $company, $job)
    {
        $this->user = $user;
        $this->company = $company;
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
        if ($this->user->role == 'candidate') {
            return ['database', 'mail'];
        } else {
            return ['database'];
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Send email using the template 'apply_job_notification'
        $type = 'apply_job_notification';
        $formatted_mail_data = getFormattedTextByType($type, [
            'user_name' => $this->user->name,
            'company_name' => $this->company->name,
            'job_title' => $this->job->title,
        ]);
        $subject = $formatted_mail_data['subject'];
        $message = $formatted_mail_data['message'];

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action(__('view'), route('job.show', $this->job->slug))
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
            'title' => __('username_has_applied_your_job', ['username' => ucfirst($this->user->name)]),
            'url' => route('company.myjob'),
            'title2' => __('you_have_applied_the_job_of_companyname', ['companyname' => $this->company->name]),
            'url2' => route('company.myjob'),
        ];
    }
}
