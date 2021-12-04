<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Traits\Macroable;
use function is_array;

class TokenNotification extends Notification implements ShouldQueue
{
    use Queueable, Macroable;

    /**
     * The token.
     *
     * @var OtpToken
     */
    public OtpToken $token;

    /**
     * TokenNotification constructor.
     *
     * @param  OtpToken  $token
     */
    public function __construct(OtpToken $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        $channels = $this->notifiableHasOtpChannels($notifiable)
            ? $notifiable->otpChannels()
            : config('otp.default_channels');

        return is_array($channels)
            ? $channels
            : array_map('trim', explode(',', $channels));
    }

    /**
     * Get the mail presentation of the notification.
     *
     * @return MailMessage
     */
    public function toMail(): MailMessage
    {
        $plainText = $this->token->plainText();

        return (new MailMessage())
            ->subject(trans('erdemkeren.otp.token-notification.email.subject'))
            ->subject(trans('erdemkeren.otp.token-notification.email.greeting'))
            ->subject(trans('erdemkeren.otp.token-notification.email.line1', $plainText))
            ->subject(trans('erdemkeren.otp.token-notification.email.line2', $plainText))
            ->subject(trans('erdemkeren.otp.token-notification.email.line3', $plainText));
    }

    /**
     * Get the sms presentation of the notification.
     *
     * @return string
     */
    public function toSms(): string
    {
        return trans('erdemkeren.otp.token-notification.sms', $this->token->plainText());
    }

    /**
     * Determine if the notifiable has otp channels or not.
     *
     * @param  mixed  $notifiable
     * @return bool
     */
    private function notifiableHasOtpChannels(mixed $notifiable): bool
    {
        return ! is_null($notifiable)
        && method_exists($notifiable, 'otpChannels')
        && ! empty($notifiable->otpChannels());
    }
}
