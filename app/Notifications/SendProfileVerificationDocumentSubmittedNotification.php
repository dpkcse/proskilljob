<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendProfileVerificationDocumentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Send email using the template 'verification_document_submitted'
        $type = 'verification_document_submitted';
        $formatted_mail_data = getFormattedTextByType($type, [
            'user_name' => $this->company->user->name,
            'company_name' => config('app.name'),
            'document_url' => route('admin.company.documents', $this->company),
        ]);
        $subject = $formatted_mail_data['subject'];
        $message = $formatted_mail_data['message'];

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action(__('view'), route('admin.company.documents', $this->company))
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
            'title' => __('a_new_profile_verification_document_is_available_for_approval'),
            'url' => route('admin.company.documents', $this->company),
        ];
    }
}
