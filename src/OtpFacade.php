<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Support\Facades\Facade;

/**
 * @method static OtpToken|null retrieveByCipherText(string $cipherText)
 * @method static OtpToken create(int|string $authenticableId, string $format = 'default')
 * @method static void sendOtpNotification(object $notifiable, OtpToken $token)
 * @method static void sendNewOtp(object $notifiable)
 */
class OtpFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'otp';
    }
}
