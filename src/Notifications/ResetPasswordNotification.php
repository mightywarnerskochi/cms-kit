<?php

namespace CMS\SiteManager\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use CMS\SiteManager\Models\CmsKit\SiteInformation;
use Illuminate\Support\Facades\Storage;

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

        $siteInfo = SiteInformation::first();
        $siteName = $siteInfo?->company_name ?: config('cms-kit.common.name', config('app.name', 'Laravel'));
        $logoUrl = null;

        if (!empty($siteInfo?->logo)) {
            $logoUrl = asset(Storage::disk('public')->url($siteInfo->logo));
        }

        return (new MailMessage)
            ->subject($siteName . ' Password Reset')
            ->view('cms-kit::emails.reset-password', [
                'resetUrl' => $resetUrl,
                'siteName' => $siteName,
                'logoUrl' => $logoUrl,
                'expireMinutes' => config('auth.passwords.cms_admins.expire'),
            ]);
    }
}
