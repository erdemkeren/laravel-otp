<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Support\Facades\Facade;
use Erdemkeren\Otp\Contracts\FormatContract;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @see OtpService
 * @method static OtpToken|null retrieveByPlainText(string $plainText)
 * @method static OtpToken|null retrieveByCipherText(string $cipherText)
 * @method static OtpToken create(int|string $authenticableId, string $format = 'default')
 * @method static bool save(OtpToken $token)
 * @method static OtpToken extend(OtpToken $token, int $secs)
 * @method static OtpToken refresh(OtpToken $token)
 * @method static OtpToken invalidate(OtpToken $token)
 * @method static void addFormat(FormatContract $format)
 * @method static void sendOtpNotification(object $notifiable, OtpToken $token)
 * @method static void sendNewOtp(Authenticatable $user)
 */
class OtpFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'otp';
    }
}
