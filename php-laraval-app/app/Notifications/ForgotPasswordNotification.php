<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgotPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $token,
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
    public function toMail(User $user): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token]);
        $url .= '?email='.$user->email;

        $mail = new MailMessage();
        $mail->subject(__('reset password'));
        $mail->view('notifications.email.forgot-password', [
            'url' => $url,
            'user' => $user->full_name,
        ]);

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $user): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
