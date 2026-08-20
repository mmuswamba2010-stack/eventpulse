<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('Reset password notification subject'))
            ->greeting(__('Reset password notification greeting'))
            ->line(__('Reset password notification line1'))
            ->action(__('Reset password notification action'), $url)
            ->line(__('Reset password notification expire', ['count' => $expire]))
            ->line(__('Reset password notification line2'));
    }
}
