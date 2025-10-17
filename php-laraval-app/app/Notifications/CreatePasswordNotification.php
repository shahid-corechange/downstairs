<?php

namespace App\Notifications;

use App\Models\User;
use Crypt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use URL;

class CreatePasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $user
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        $hash = $this->user->generateCreatePasswordHash();
        $url = URL::temporarySignedRoute(
            'password.create',
            now()->addMinutes(60),
            ['hash' => $hash, 'payload' => Crypt::encryptString($this->user->email)]
        );

        $mail = new MailMessage();
        $mail->subject(__('create password'));
        $mail->view('notifications.email.create-password', [
            'url' => $url, 'user' => $this->user->full_name,
        ]);

        return $mail;
    }
}
