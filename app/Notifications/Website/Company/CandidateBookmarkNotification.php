<?php

namespace App\Notifications\Website\Company;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateBookmarkNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $company;

    public $candidate;

    public function __construct($company, $candidate)
    {
        $this->company = $company;
        $this->candidate = $candidate;
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
        // return (new MailMessage)
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', url('/'))
        //             ->line('Thank you for using our application!');
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
            'title' => __('companyname_has_bookmarked_you', ['companyname' => ucfirst($this->company->name)]),
            'url' => route('website.employe.details', $this->company->username),
            'title2' => __('You_have_bookmarked_username', ['username' => $this->candidate->user->name]),
            'url2' => route('website.candidate.details', $this->candidate->user->username),
        ];
    }
}
