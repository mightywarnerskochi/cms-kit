<?php

namespace CMS\SiteManager\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Get the reset password notification mail message for the given token.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = route('cms.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in ' . config('auth.passwords.cms_admins.expire') . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
