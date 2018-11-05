<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class TokenNotification.
 */
final class TokenNotification extends Notification implements ShouldQueue
{
    use Queueable, Macroable;

    /**
     * The token generated.
     *
     * @var Token
     */
    public $token;

    /**
     * TokenGenerated constructor.
     *
     * @param Token $token
     */
    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        $channels = method_exists($notifiable, 'otpChannels') && ! empty($notifiable->otpChannels())
            ? $notifiable->otpChannels()
            : config('temporary_access.default_channels');

        return \is_array($channels)
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
        return (new MailMessage())
            ->greeting('Hello!')
            ->line('Somebody recently requested for a one-time password in behalf of you.')
            ->line('You can enter the following reset code: '.$this->token->plainText())
            ->line('If you didn\'t request the password, simply ignore this message.');
    }

    /**
     * Get the sms presentation of the notification.
     *
     * @return string
     */
    public function toSms(): string
    {
        $message = 'Somebody recently requested a one-time password.';
        $message .= "You can enter the following reset code: {$this->code} ";
        $message .= "If you didn't request the password, simply ignore this message.";

        return $message;
    }
}
