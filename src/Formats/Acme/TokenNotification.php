<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Formats\Acme;

use Erdemkeren\Otp\OtpToken;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TokenNotification extends Notification
{
    public function __construct(private OtpToken $token)
    {
    }

    public function via(?object $notifiable): array
    {
        $channels = ! is_null($notifiable) && method_exists($notifiable, 'otpChannels') && ! empty($notifiable->otpChannels())
            ? $notifiable->otpChannels()
            : config('otp.default_channels');

        return \is_array($channels)
            ? $channels
            : array_map('trim', explode(',', $channels));
    }

    public function toMail()
    {
        return tap(new MailMessage(), function (MailMessage $mail): void {
            $mail->subject($this->token->plainText());
        });
    }
}
