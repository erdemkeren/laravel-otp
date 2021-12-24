<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Formats\Acme;

use Closure;
use Erdemkeren\Otp\Contracts\FormatContract;
use Erdemkeren\Otp\OtpToken;

class Acme implements FormatContract
{
    public function name(): string
    {
        return 'acme';
    }

    public function generator(): Closure
    {
        return fn(): string => ':generator:';
    }

    public function createNotification(OtpToken $token): object
    {
        return new TokenNotification($token);
    }

}
