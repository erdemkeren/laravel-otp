<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Test\TestFormat;

use Erdemkeren\Otp\OtpToken;

class AcmeNotification
{
    public function __construct(
        private OtpToken $otpToken
    )
    {
    }
}
