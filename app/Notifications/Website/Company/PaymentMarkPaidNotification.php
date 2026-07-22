<?php

namespace App\Notifications\Website\Company;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentMarkPaidNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        // Send email using the template 'payment_mark_paid_notification'
        $type = 'payment_mark_paid_notification';
        $formatted_mail_data = getFormattedTextByType($type, [
            'user_name' => $notifiable->name,
        ]);
        $subject = $formatted_mail_data['subject'];
        $message = $formatted_mail_data['message'];

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action(__('view_plan'), route('company.plan'))
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
            'title' => __('your_payment_has_been_marked_as_paid_you_can_now_start_working_on_your_job'),
            'url' => route('company.plan'),
        ];
    }
}
